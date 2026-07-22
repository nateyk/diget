<?php

namespace App\Services;

use App\Models\User;
use App\Models\UsernameHistory;
use Illuminate\Support\Facades\Schema;

class UsernameResolver
{
    public function __construct(protected UsernamePolicy $policy)
    {
    }

    public function owner(string $username): ?User
    {
        $normalized = $this->policy->normalize($username);
        $current = User::where('username', $normalized)->active()->first();

        if ($current || !Schema::hasTable('username_histories')) {
            return $current;
        }

        $history = UsernameHistory::where('old_username', $normalized)->first();

        return $history ? User::whereKey($history->user_id)->active()->first() : null;
    }

    public function publicProfile(string $username, bool $authorOnly = false): array
    {
        $normalized = $this->policy->normalize($username);
        $query = User::where('username', $normalized)->active()->whereDataCompleted();

        if ($authorOnly) {
            $query->author();
        }

        $current = $query->first();
        if ($current) {
            return [$current, false];
        }

        if (!Schema::hasTable('username_histories')) {
            return [null, false];
        }

        $history = UsernameHistory::where('old_username', $normalized)->first();
        if (!$history) {
            return [null, false];
        }

        $query = User::whereKey($history->user_id)->active()->whereDataCompleted();
        if ($authorOnly) {
            $query->author();
        }

        return [$query->first(), true];
    }
}
