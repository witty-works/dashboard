<?php

namespace Tests\Feature;

use App\Jobs\SyncUserToNlpApi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The failed-job mail goes to the deployment's support address. Without
 * one, the job is still recorded in failed_jobs: a notification to an empty
 * address threw inside the JobFailed listener, before the worker logged
 * the job.
 */
class FailedJobMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_failed_job_is_recorded_without_a_support_address(): void
    {
        config([
            'failed-job-monitor.mail.to' => '',
            'queue.default' => 'database',
            'app.nlp_api_endpoint.urls' => ['http://nlp.test'],
            'app.nlp_api_endpoint.sync_rules' => true,
        ]);
        $user = User::factory()->withPersonalTeam()->create();
        DB::table('jobs')->delete();
        Http::fake(['nlp.test/*' => Http::response('down', 503)]);

        SyncUserToNlpApi::dispatch($user, 'high');
        $this->travel(25)->hours();
        $this->artisan('queue:work', ['connection' => 'database', '--once' => true, '--queue' => 'high'])->assertSuccessful();

        $this->assertSame(1, DB::table('failed_jobs')->count());
    }

    public function test_an_empty_support_address_falls_back_to_the_sender(): void
    {
        $config = $this->withEnvironment(
            ['SUPPORT_EMAIL' => '', 'MAIL_FROM_ADDRESS' => 'dashboard@deployment.example'],
            fn () => [require base_path('config/failed-job-monitor.php'), require base_path('config/app.php')],
        );

        $this->assertSame('dashboard@deployment.example', $config[0]['mail']['to']);
        $this->assertSame('dashboard@deployment.example', $config[1]['support_email']);
    }

    /** Runs $read with these variables set wherever env() looks, then restores them. */
    private function withEnvironment(array $values, callable $read): mixed
    {
        $saved = [];
        foreach ($values as $name => $value) {
            $saved[$name] = [$_SERVER[$name] ?? null, $_ENV[$name] ?? null, getenv($name)];
            $_SERVER[$name] = $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
        try {
            return $read();
        } finally {
            foreach ($saved as $name => [$server, $env, $getenv]) {
                if ($server === null) { unset($_SERVER[$name]); } else { $_SERVER[$name] = $server; }
                if ($env === null) { unset($_ENV[$name]); } else { $_ENV[$name] = $env; }
                putenv($getenv === false ? $name : "{$name}={$getenv}");
            }
        }
    }
}
