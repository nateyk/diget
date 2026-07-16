<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoSeedCommand extends Command
{
    protected $signature = 'demo:seed {--fresh : Reset the local database after confirmation}';
    protected $description = 'Seed repeatable local Diget demo marketplace data';

    public function handle(): int
    {
        if ($this->laravel->environment('production')) {
            $this->error('Demo data seeding is disabled in production.');
            return self::FAILURE;
        }
        if (!$this->laravel->environment(['local', 'testing'])) {
            $this->warn('Demo data should only be used in local or testing environments.');
        }
        if ($this->option('fresh')) {
            if (!$this->confirm('This will erase the current database. Continue?', false)) {
                return self::INVALID;
            }
            $this->call('migrate:fresh');
        }
        $this->call('db:seed', ['--class' => 'DemoDatabaseSeeder']);
        return self::SUCCESS;
    }
}
