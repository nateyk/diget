<?php

namespace Tests\Feature;

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
}
