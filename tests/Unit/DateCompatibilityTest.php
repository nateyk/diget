<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class DateCompatibilityTest extends TestCase
{
    public function test_carbon_parses_and_preserves_timezone(): void
    {
        $date = Carbon::parse('2026-01-15 12:30:00', 'Africa/Addis_Ababa');

        $this->assertSame('Africa/Addis_Ababa', $date->getTimezone()->getName());
        $this->assertSame('2026-01-15 12:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function test_carbon_humanizes_elapsed_time(): void
    {
        $date = Carbon::parse('2026-01-15 12:00:00', 'UTC');
        $comparison = Carbon::parse('2026-01-14 12:00:00', 'UTC');

        $this->assertSame('1 day after', $date->diffForHumans($comparison));
    }

    public function test_date_format_preserves_english_output(): void
    {
        app()->setLocale('en');

        $this->assertSame('Jan 15, 2026', dateFormat('2026-01-15', 'M d, Y'));
    }

    public function test_carbon_translates_weekday_and_month_names(): void
    {
        $date = Carbon::parse('2026-01-15')->locale('en');
        $amharicDate = Carbon::parse('2026-01-15')->locale('am');

        $this->assertSame('Thursday, 15 January 2026', $date->translatedFormat('l, j F Y'));
        $this->assertSame('ሐሙስ, 15 ጃንዩወሪ 2026', $amharicDate->translatedFormat('l, j F Y'));
    }
}
