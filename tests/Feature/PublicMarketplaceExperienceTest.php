<?php

namespace Tests\Feature;

use App\Models\Item;
use Database\Seeders\UiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMarketplaceExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->detectEnvironment(fn (): string => 'testing');
        app(UiDemoSeeder::class)->run();
    }

    public function test_demo_creator_storefront_and_discover_products_are_public(): void
    {
        $this->get('/@selamstudio')
            ->assertOk()
            ->assertSee('Selam Studio')
            ->assertSee('Modern Portfolio Template');

        $this->get(route('items.index'))
            ->assertOk()
            ->assertSee('All products')
            ->assertSee('Laravel SaaS Starter Kit');

        $this->get('/categories/ui-demo-code')
            ->assertOk()
            ->assertSee('Code')
            ->assertSee('Laravel SaaS Starter Kit');
    }

    public function test_product_detail_preserves_purchase_and_creator_context(): void
    {
        $item = Item::where('slug', 'modern-portfolio-template')->firstOrFail();

        $this->get($item->getLink())
            ->assertOk()
            ->assertSee('Get this product')
            ->assertSee('Purchase')
            ->assertSee('Creator')
            ->assertSee('Selam Studio');
    }

    public function test_pending_products_do_not_appear_in_public_catalog(): void
    {
        $approved = Item::where('slug', 'laravel-saas-starter-kit')->firstOrFail();
        $pending = $approved->replicate();
        $pending->slug = 'ui-demo-pending-product';
        $pending->name = 'Pending UI Demo Product';
        $pending->status = Item::STATUS_PENDING;
        $pending->save();

        $this->get(route('items.index'))
            ->assertOk()
            ->assertDontSee('Pending UI Demo Product');
    }

    public function test_free_and_discounted_products_keep_their_public_price_states(): void
    {
        $free = Item::where('slug', 'small-business-accounting-template')->firstOrFail();
        $discounted = Item::where('slug', 'modern-portfolio-template')->firstOrFail();

        $this->get($free->getLink())
            ->assertOk()
            ->assertSee('Free');

        $this->get($discounted->getLink())
            ->assertOk()
            ->assertSee('315')
            ->assertSee('420');
    }

    public function test_creator_with_no_products_has_a_clear_empty_storefront_state(): void
    {
        $this->get('/@abenezerdesign')
            ->assertOk()
            ->assertSee('No products published yet')
            ->assertSee('About this creator');
    }
}
