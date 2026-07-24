<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BasicThemeDropdownUiTest extends TestCase
{
    public function test_basic_theme_uses_one_shared_styled_dropdown_system(): void
    {
        $root = dirname(__DIR__, 2);
        $styles = file_get_contents($root . '/resources/views/themes/basic/includes/styles.blade.php');
        $scripts = file_get_contents($root . '/resources/views/themes/basic/includes/scripts.blade.php');
        $profile = file_get_contents($root . '/resources/views/themes/basic/workspace/settings/profile.blade.php');
        $items = file_get_contents($root . '/resources/views/themes/basic/workspace/items/index.blade.php');
        $css = file_get_contents($root . '/public/themes/basic/assets/css/app.css');

        $this->assertSame(1, substr_count($styles, 'bootstrap-select.min.css'));
        $this->assertSame(1, substr_count($scripts, 'bootstrap-select.min.js'));
        $this->assertStringContainsString('dashboard-picker drop-down', $profile);
        $this->assertStringContainsString("option.classList.toggle('active'", $profile);
        $this->assertStringContainsString("item.classList.toggle('active'", $items);
        $this->assertStringContainsString('.bootstrap-select .dropdown-item.selected', $css);
        $this->assertStringContainsString('.drop-down .drop-down-menu .drop-down-item:focus-visible', $css);
    }
}
