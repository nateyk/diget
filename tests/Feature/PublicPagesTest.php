<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPublicPageFixture();
    }

    public function test_public_entry_pages_are_available(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/password/reset')->assertOk();
        $this->get('/discover')->assertOk();
    }

    public function test_creator_and_product_pages_render_with_isolated_demo_records(): void
    {
        $product = Item::query()->where('slug', 'ui-product')->firstOrFail();

        $this->get('/@ui-creator')->assertOk();
        $this->get('/@ui-creator/portfolio')->assertOk();
        $this->get('/items/' . $product->slug . '/' . $product->id)->assertOk();
    }

    public function test_workspace_dashboard_remains_protected_for_guests(): void
    {
        $this->get('/workspace/dashboard')->assertRedirect(route('login'));
    }

    private function seedPublicPageFixture(): void
    {
        $this->putSettings('actions', [
            'registration' => true,
            'gdpr_cookie' => false,
            'force_ssl' => false,
            'email_verification' => false,
        ]);
        $this->putSettings('general', [
            'site_name' => 'Diget',
            'contact_email' => 'support@example.test',
            'date_format' => '10',
            'timezone' => 'UTC',
        ]);
        $this->putSettings('profile', [
            'default_avatar' => 'images/profiles/default/avatar.png',
            'default_cover' => 'images/profiles/default/cover.png',
        ]);
        $this->putSettings('item', [
            'reviews_status' => false,
            'comments_status' => false,
            'changelogs_status' => false,
        ]);
        $this->putSettings('currency', [
            'code' => 'USD',
            'symbol' => '$',
            'position' => 1,
        ]);
        $this->putSettings('seo', [
            'description' => 'Creator storefronts for digital products.',
            'keywords' => 'creator, storefront',
        ]);
        $this->putSettings('links', []);
        $this->putSettings('social_links', []);
        $this->putSettings('smtp', ['status' => true]);

        DB::table('storage_providers')->updateOrInsert(
            ['alias' => 'local'],
            ['name' => 'Local', 'processor' => 'Local', 'credentials' => null]
        );

        $category = Category::query()->updateOrCreate(
            ['slug' => 'digital-products'],
            ['name' => 'Digital Products', 'title' => 'Digital Products', 'description' => 'Products for creators.']
        );

        $creator = User::query()->updateOrCreate(
            ['username' => 'ui-creator'],
            [
                'firstname' => 'UI',
                'lastname' => 'Creator',
                'email' => 'ui-creator@example.test',
                'password' => bcrypt('TestPassword123!'),
                'is_author' => User::AUTHOR,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
                'profile_heading' => 'Digital creator',
                'profile_card_description' => 'A focused creator storefront for practical digital products.',
                'profile_description' => '<p>A focused creator storefront for practical digital products.</p>',
            ]
        );

        Item::query()->updateOrCreate(
            ['slug' => 'ui-product'],
            [
                'author_id' => $creator->id,
                'name' => 'UI Product',
                'description' => '<p>A practical digital product.</p>',
                'category_id' => $category->id,
                'tags' => 'creator, product',
                'preview_image' => 'images/items/ui-product.png',
                'main_file' => 'files/ui-product.zip',
                'regular_price' => 12,
                'extended_price' => 24,
                'status' => Item::STATUS_APPROVED,
            ]
        );
    }

    private function putSettings(string $key, array $value): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => json_encode($value)]
        );
    }
}
