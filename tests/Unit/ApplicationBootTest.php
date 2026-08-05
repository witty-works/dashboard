<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Guards the framework upgrade: if the container, config or service providers
 * fail to boot, every other test fails for the same reason and this one names it.
 */
class ApplicationBootTest extends TestCase
{
    public function test_application_boots(): void
    {
        $this->assertTrue($this->app->isBooted());
    }

    public function test_environment_is_testing(): void
    {
        $this->assertSame('testing', $this->app->environment());
    }

    public function test_encryption_key_is_configured(): void
    {
        $this->assertNotEmpty(config('app.key'));
    }
}
