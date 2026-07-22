<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UsernameLock
{
    public function run(array $usernames, Closure $callback): mixed
    {
        $connection = DB::connection();

        if ($connection->getDriverName() !== 'mysql') {
            return $callback();
        }

        $keys = collect($usernames)
            ->filter()
            ->unique()
            ->sort()
            ->map(fn (string $username) => 'diget_username_' . hash('sha1', $username))
            ->values();
        $acquired = [];

        try {
            foreach ($keys as $key) {
                $result = $connection->selectOne('SELECT GET_LOCK(?, 10) AS acquired', [$key]);

                if ((int) ($result->acquired ?? 0) !== 1) {
                    throw ValidationException::withMessages([
                        'username' => translate('Username availability changed. Please try again.'),
                    ]);
                }

                $acquired[] = $key;
            }

            return $callback();
        } finally {
            foreach (array_reverse($acquired) as $key) {
                $connection->selectOne('SELECT RELEASE_LOCK(?) AS released', [$key]);
            }
        }
    }
}
