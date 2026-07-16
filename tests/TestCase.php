<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function tearDown(): void
    {
        while (ob_get_level() > 1) {
            ob_end_clean();
        }

        parent::tearDown();
    }
}
