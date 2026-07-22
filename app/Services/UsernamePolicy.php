<?php

namespace App\Services;

use App\Models\Settings;
use App\Models\User;
use App\Models\UsernameHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Throwable;

class UsernamePolicy
{
    public function normalize(?string $value): string
    {
        $username = trim((string) $value);

        if (str_starts_with($username, '@')) {
            $username = substr($username, 1);
        }

        return Str::lower(trim($username));
    }

    public function blacklist(): Collection
    {
        try {
            $setting = Settings::selectSettings('username');
            $raw = $setting ? (string) ($setting->blacklisted_usernames ?? '') : '';
        } catch (Throwable $e) {
            $raw = '';
        }

        return collect(explode(',', $raw))
            ->map(fn ($value) => $this->normalize((string) $value))
            ->filter()
            ->unique()
            ->values();
    }

    public function isSystemReserved(string $username): bool
    {
        return in_array($this->normalize($username), config('usernames.reserved', []), true);
    }

    public function isBlacklisted(string $username): bool
    {
        return $this->blacklist()->contains($this->normalize($username));
    }

    public function isHistoricallyReserved(string $username, ?User $exceptUser = null): bool
    {
        if (!Schema::hasTable('username_histories')) {
            return false;
        }

        return UsernameHistory::where('old_username', $this->normalize($username))->exists();
    }

    public function isAvailable(string $username, ?User $exceptUser = null): bool
    {
        return !User::where('username', $this->normalize($username))
            ->when($exceptUser, fn ($query) => $query->whereKeyNot($exceptUser->id))
            ->exists();
    }

    public function errors(?string $value, ?User $exceptUser = null): array
    {
        $username = $this->normalize($value);

        if ($exceptUser && $username === $this->normalize($exceptUser->username)) {
            return [];
        }

        $length = mb_strlen($username);
        if ($length < config('usernames.min_length', 6) || $length > config('usernames.max_length', 50)) {
            return [translate('Username must be between 6 and 50 characters.')];
        }

        // Mirrors the existing alpha_dash contract while rejecting separators and control characters.
        if (!preg_match('/^[\pL\pM\pN_-]+$/u', $username) || preg_match('/[\pC\pZ]/u', $username)) {
            return [translate('Username may only contain letters, numbers, dashes, and underscores.')];
        }

        if ($this->isSystemReserved($username) || $this->isBlacklisted($username)) {
            return [translate('This username is not available.')];
        }

        if (!$this->isAvailable($username, $exceptUser) || $this->isHistoricallyReserved($username, $exceptUser)) {
            return [translate('This username is already in use.')];
        }

        return [];
    }

    public function assertSelectable(?string $value, ?User $exceptUser = null): string
    {
        $username = $this->normalize($value);
        $errors = $this->errors($username, $exceptUser);

        if ($errors) {
            throw ValidationException::withMessages(['username' => $errors]);
        }

        return $username;
    }

    public function latestChange(User $user): ?UsernameHistory
    {
        if (!Schema::hasTable('username_histories')) {
            return null;
        }

        return $user->usernameHistories()->latest('changed_at')->first();
    }

    public function nextChangeAt(User $user): ?Carbon
    {
        $latest = $this->latestChange($user);

        return $latest?->changed_at->copy()->addDays(config('usernames.cooldown_days', 30));
    }

    public function canChange(User $user, ?Carbon $at = null): bool
    {
        $next = $this->nextChangeAt($user);

        return !$next || ($at ?? now())->greaterThanOrEqualTo($next);
    }

    public function assertCooldown(User $user): void
    {
        if (!$this->canChange($user)) {
            throw ValidationException::withMessages([
                'username' => translate('You can change your username again on :date.', [
                    'date' => $this->nextChangeAt($user)->toDayDateTimeString(),
                ]),
            ]);
        }
    }

    public function isUsernameConstraintViolation(QueryException $exception): bool
    {
        if (!in_array((string) $exception->getCode(), ['23000', '23505'], true)) {
            return false;
        }

        $message = Str::lower($exception->getMessage());

        return Str::contains($message, [
            'users_username_unique',
            'username_histories_old_username_unique',
            'unique constraint failed: users.username',
            'unique constraint failed: username_histories.old_username',
        ]);
    }
}
