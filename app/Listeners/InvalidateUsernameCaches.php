<?php

namespace App\Listeners;

use App\Events\UsernameChanged;
use Illuminate\Support\Facades\Cache;
use Throwable;

class InvalidateUsernameCaches
{
    public function handle(UsernameChanged $event): void
    {
        try {
            Cache::forget('home_featured_author_cache_v2');
        } catch (Throwable $e) {
            report($e);
        }
    }
}
