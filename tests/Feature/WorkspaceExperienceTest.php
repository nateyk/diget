<?php

namespace Tests\Feature;

use App\Actions\ChangeUsername;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\UiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app()->detectEnvironment(fn (): string => 'testing');
        app(UiDemoSeeder::class)->run();
    }

    public function test_creator_dashboard_renders_the_shared_workspace_shell(): void
    {
        $creator = User::query()->where('username', 'nahomdeveloper')->firstOrFail();

        $this->actingAs($creator)
            ->get(route('workspace.dashboard'))
            ->assertOk()
            ->assertSee('workspace-page-header', false)
            ->assertSee('workspace-stat-card', false)
            ->assertSee('workspace-sidebar', false)
            ->assertSee('aria-label="Workspace navigation"', false)
            ->assertDontSee('dashboard-sidebar-links-title', false)
            ->assertDontSee('data-simplebar', false)
            ->assertSee('class="dashboard-balance"', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('form="logout-form"', false)
            ->assertSee('aria-controls="workspaceSidebar"', false)
            ->assertSee('Total Sales');
    }

    public function test_buyer_can_view_purchases_but_cannot_open_creator_dashboard(): void
    {
        $buyer = User::query()->where('username', 'buyerdemo')->firstOrFail();

        $this->actingAs($buyer)
            ->get(route('workspace.purchases.index'))
            ->assertOk()
            ->assertSee('Purchases')
            ->assertSee('Become an Author')
            ->assertSee('href="'.route('workspace.become-an-author').'"', false)
            ->assertSee('type="submit" class="drop-down-item text-danger" form="logout-form"', false)
            ->assertSee('workspace-data-table', false)
            ->assertSee('<th scope="col">', false);

        $this->actingAs($buyer)
            ->get(route('workspace.dashboard'))
            ->assertForbidden();
    }

    public function test_purchase_search_uses_a_contextual_empty_state(): void
    {
        $buyer = User::query()->where('username', 'buyerdemo')->firstOrFail();

        $this->actingAs($buyer)
            ->get(route('workspace.purchases.index', ['search' => 'no-matching-purchase']))
            ->assertOk()
            ->assertSee('No purchases match the current search.');
    }

    public function test_creator_items_use_one_labeled_action_menu_per_row(): void
    {
        $creator = User::query()->where('username', 'nahomdeveloper')->firstOrFail();

        $this->actingAs($creator)
            ->get(route('workspace.items.index'))
            ->assertOk()
            ->assertSee('aria-label="Item actions"', false)
            ->assertSee('dropdown-menu-end', false);
    }

    public function test_creator_product_form_uses_the_simplified_creation_flow(): void
    {
        $creator = User::query()->where('username', 'nahomdeveloper')->firstOrFail();
        $category = Category::query()->firstOrFail();

        $this->actingAs($creator)
            ->get(route('workspace.items.create', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee('workspace-item-create-form', false)
            ->assertSee('Product Details')
            ->assertSee('Product Files')
            ->assertSee('Pricing And Publishing')
            ->assertSee('Create Product')
            ->assertSee('name="category" value="'.$category->slug.'"', false)
            ->assertSee('regular_license_price', false)
            ->assertSee('name="main_file"', false)
            ->assertDontSee('Category And Attributes')
            ->assertDontSee('<select class="form-select form-select-md" disabled>', false);
    }

    public function test_account_details_use_two_columns_and_ignore_legacy_exclusivity_input(): void
    {
        $creator = User::query()->where('username', 'nahomdeveloper')->firstOrFail();
        $originalExclusivity = $creator->exclusivity;

        $this->actingAs($creator)
            ->get(route('workspace.settings.index'))
            ->assertOk()
            ->assertSee('workspace-account-form', false)
            ->assertSee('col-12 col-lg-6', false)
            ->assertDontSee('Exclusivity of Your Items')
            ->assertDontSee('name="exclusivity"', false);

        $this->actingAs($creator)
            ->post(route('workspace.settings.update'), [
                'firstname' => $creator->firstname,
                'lastname' => $creator->lastname,
                'email' => $creator->email,
                'address_line_1' => 'Bole Road',
                'address_line_2' => '',
                'city' => 'Addis Ababa',
                'state' => 'Addis Ababa',
                'zip' => '1000',
                'country' => 'ET',
                'exclusivity' => User::AUTHOR_EXCLUSIVE,
            ])
            ->assertRedirect();

        $this->assertSame($originalExclusivity, $creator->fresh()->exclusivity);
    }

    public function test_username_settings_show_public_url_warning_and_cooldown_state(): void
    {
        $creator = User::query()->where('username', 'nahomdeveloper')->firstOrFail();

        $this->actingAs($creator)
            ->get(route('workspace.settings.index'))
            ->assertOk()
            ->assertSee('<span class="input-group-text">@</span>', false)
            ->assertSee('aria-describedby="usernameHelp usernameWarning"', false)
            ->assertSee($creator->getProfileLink(), false)
            ->assertSee('you can change again after 30 days', false);

        app(ChangeUsername::class)->execute($creator, 'nahom-renamed', $creator);

        $this->actingAs($creator->fresh())
            ->get(route('workspace.settings.index'))
            ->assertOk()
            ->assertSee('You can change your username again on')
            ->assertSee('disabled', false);
    }
}
