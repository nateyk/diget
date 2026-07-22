<?php

namespace App\Console\Commands;

use Database\Seeders\UiDemoSeeder;
use Illuminate\Console\Command;

class UiDemoSeedCommand extends Command
{
    protected $signature = 'ui-demo:seed';

    protected $description = 'Seed repeatable local creator storefront and product UI records';

    public function handle(): int
    {
        if ($this->laravel->environment('production')) {
            $this->error('UI demo seeding is disabled in production.');

            return self::FAILURE;
        }

        $this->call('db:seed', ['--class' => UiDemoSeeder::class]);

        return self::SUCCESS;
    }
}
