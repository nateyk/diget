<?php

namespace Tests\Unit;

use Database\Seeders\DemoDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_is_blocked_in_production(): void
    {
        app()->detectEnvironment(fn (): string => 'production');
        $this->expectException(\RuntimeException::class);
        app(DemoDatabaseSeeder::class)->run();
    }

    public function test_demo_seeder_is_repeatable_and_creates_valid_demo_data(): void
    {
        app()->detectEnvironment(fn (): string => 'testing');
        config([
            'demo.user_password' => 'TestDemoPassword123!',
            'demo.super_admin_password' => 'TestDemoPassword123!',
        ]);

        $seeder = app(DemoDatabaseSeeder::class);
        $seeder->run();
        $firstCounts = $this->demoCounts();
        $seeder->run();

        $this->assertSame($firstCounts, $this->demoCounts());
        $this->assertDatabaseHas('users', ['username' => 'abenezerdesign', 'is_author' => 1]);
        $this->assertDatabaseHas('users', ['username' => 'disabledbuyer', 'status' => 0]);
        $this->assertDatabaseHas('items', ['slug' => 'laravel-saas-starter-kit']);
        $this->assertTrue(Hash::check('TestDemoPassword123!', \App\Models\User::where('username', 'buyer1')->value('password')));
        $this->assertFileExists(storage_path('app/demo/items/laravel-saas-starter-kit.zip'));
        $this->assertDatabaseHas('pages', ['slug' => 'about']);
        $this->assertDatabaseHas('blog_articles', ['slug' => 'demo-creator-guide-1']);
        $this->assertDatabaseHas('themes', ['alias' => 'basic']);
        $this->assertDatabaseHas('settings', ['key' => 'social_links']);
        $this->assertDatabaseHas('settings', ['key' => 'links']);
    }

    private function demoCounts(): array
    {
        return collect(['admins', 'reviewers', 'users', 'categories', 'sub_categories', 'items', 'item_reviews', 'item_comments', 'purchases', 'sales', 'transactions', 'withdrawals', 'refunds', 'tickets', 'blog_articles'])
            ->mapWithKeys(fn ($table) => [$table => \DB::table($table)->count()])
            ->all();
    }
}
