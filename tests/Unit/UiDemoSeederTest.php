<?php

namespace Tests\Unit;

use App\Models\Item;
use Database\Seeders\UiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_ui_demo_seeder_is_blocked_in_production(): void
    {
        app()->detectEnvironment(fn (): string => 'production');

        $this->expectException(\RuntimeException::class);

        app(UiDemoSeeder::class)->run();
    }

    public function test_ui_demo_seeder_is_idempotent_and_creates_public_records(): void
    {
        app()->detectEnvironment(fn (): string => 'testing');

        $seeder = app(UiDemoSeeder::class);
        $seeder->run();
        $firstCounts = $this->demoCounts();
        $seeder->run();

        $this->assertSame($firstCounts, $this->demoCounts());
        $this->assertDatabaseHas('users', ['username' => 'selamstudio', 'is_author' => 1]);
        $this->assertDatabaseHas('users', ['username' => 'abenezerdesign', 'is_author' => 1]);
        $this->assertDatabaseHas('items', ['slug' => 'laravel-saas-starter-kit', 'status' => Item::STATUS_APPROVED]);
        $this->assertDatabaseHas('items', ['slug' => 'small-business-accounting-template', 'is_free' => Item::FREE]);
        $this->assertDatabaseHas('items', ['slug' => 'modern-portfolio-template', 'is_on_discount' => Item::DISCOUNT_ON]);
        $this->assertDatabaseCount('item_reviews', 3);
        $this->assertDatabaseCount('purchases', 3);
    }

    private function demoCounts(): array
    {
        return collect([
            'users',
            'categories',
            'items',
            'item_discounts',
            'item_reviews',
            'purchases',
            'sales',
            'transactions',
            'transaction_items',
            'uploaded_files',
            'followers',
            'favorites',
        ])->mapWithKeys(fn (string $table): array => [$table => \DB::table($table)->count()])->all();
    }
}
