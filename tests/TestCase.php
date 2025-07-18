<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Route; // Import Route facade

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     *
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // FIX: Unconditionally load auth routes for all feature tests.
        // This prevents "Route [login/logout] not defined" errors when views are rendered during testing.
        Route::middleware('web')->group(base_path('routes/auth.php'));
    }
}