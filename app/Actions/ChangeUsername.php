<?php

namespace App\Actions;

use App\Events\UsernameChanged;
use App\Models\User;
use App\Models\UsernameHistory;
use App\Services\UsernamePolicy;
use App\Services\UsernameLock;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeUsername
{
    public function __construct(
        protected UsernamePolicy $policy,
        protected UsernameLock $lock,
    ) {
    }

    public function execute(
        User $user,
        string $requestedUsername,
        ?Model $actor = null,
        string $source = 'account',
        bool $bypassCooldown = false,
    ): ?UsernameHistory {
        $normalized = $this->policy->normalize($requestedUsername);

        if ($normalized === $this->policy->normalize($user->username)) {
            return null;
        }

        try {
            return $this->lock->run([
                $this->policy->normalize($user->username),
                $normalized,
            ], function () use ($user, $normalized, $actor, $source, $bypassCooldown) {
                return DB::transaction(function () use ($user, $normalized, $actor, $source, $bypassCooldown) {
                    $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

                    if ($normalized === $this->policy->normalize($lockedUser->username)) {
                        return null;
                    }

                    if (!$bypassCooldown) {
                        $this->policy->assertCooldown($lockedUser);
                    }

                    $newUsername = $this->policy->assertSelectable($normalized, $lockedUser);
                    $oldUsername = $this->policy->normalize($lockedUser->username);
                    $changedAt = now();

                    $lockedUser->username = $newUsername;
                    $lockedUser->save();

                    $history = UsernameHistory::create([
                        'user_id' => $lockedUser->id,
                        'old_username' => $oldUsername,
                        'new_username' => $newUsername,
                        'actor_type' => $actor ? get_class($actor) : null,
                        'actor_id' => $actor?->getKey(),
                        'source' => $source,
                        'changed_at' => $changedAt,
                    ]);

                    UsernameChanged::dispatch($lockedUser, $oldUsername, $newUsername, $changedAt);

                    return $history;
                }, 3);
            });
        } catch (QueryException $e) {
            if ($this->policy->isUsernameConstraintViolation($e)) {
                throw ValidationException::withMessages([
                    'username' => translate('This username is already in use.'),
                ]);
            }

            throw $e;
        }
    }
}
