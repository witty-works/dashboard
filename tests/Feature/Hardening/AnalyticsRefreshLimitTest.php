<?php

namespace Tests\Feature\Hardening;

use App\Helpers\PosthogHelper;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Any signed-in user can ask the analytics API to refresh, which skips the
 * cache and costs a PostHog API call from the deployment's rate limit. A
 * refresh is honoured only for data at least as old as the page waits before
 * it offers its refresh button (3 minutes); younger data is served from the
 * cache.
 */
class AnalyticsRefreshLimitTest extends TestCase
{
    private const FILTER = ['events' => [['id' => 'popover_open', 'properties' => []]]];

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'posthog.host' => 'https://posthog.invalid',
            'posthog.project_id' => '1',
            'posthog.personal_api_key' => 'phx_test',
            'posthog.insights_cache_time' => 3600,
            'cache.default' => 'array',
        ]);
        Http::fake(['*' => Http::response(['result' => []])]);
    }

    public function test_a_refresh_of_fresh_data_is_served_from_the_cache(): void
    {
        PosthogHelper::fetchData(self::FILTER);
        PosthogHelper::fetchData(self::FILTER, true);
        PosthogHelper::fetchData(self::FILTER, true);

        Http::assertSentCount(1);
    }

    public function test_a_refresh_of_older_data_asks_posthog_again(): void
    {
        PosthogHelper::fetchData(self::FILTER);
        $this->travel(4)->minutes();
        PosthogHelper::fetchData(self::FILTER, true);

        Http::assertSentCount(2);
    }
}
