<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * /up, which the container's HEALTHCHECK and the proxy call (routes/health.php).
 */
class HealthCheckTest extends TestCase
{
    public function test_answers_ok_when_the_database_answers(): void
    {
        $this->get(route('health'))
            ->assertOk()
            ->assertSeeText('OK');
    }

    /** Outside the `web` group: a probe must not open a session. */
    public function test_starts_no_session(): void
    {
        $this->get(route('health'))
            ->assertOk()
            ->assertCookieMissing(config('session.cookie'));
    }

    public function test_answers_503_when_the_database_is_unreachable(): void
    {
        $connection = config('database.default');
        config(["database.connections.{$connection}.host" => '127.0.0.1']);
        config(["database.connections.{$connection}.port" => 1]);
        DB::purge($connection);

        $this->get(route('health'))
            ->assertStatus(503)
            ->assertSeeText('database unreachable');
    }
}
