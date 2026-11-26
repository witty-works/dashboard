<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The analytics page draws its charts with Chart.js and moment from the
 * app's own bundle, so visitors' browsers contact no third-party CDN.
 */
class AnalyticsScriptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_analytics_page_loads_no_script_from_a_cdn(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        $response = $this->actingAs($user->refresh())->get('/en/user/analytics');

        $response->assertOk();
        $this->assertStringNotContainsString('cdnjs.cloudflare.com', $response->getContent());
        // The bundle runs deferred, so the charts load once the page is
        // parsed, and charts asked for earlier wait for it.
        $html = $response->getContent();
        $this->assertStringContainsString("document.addEventListener('DOMContentLoaded', () => load_charts(false));", $html);
        $this->assertDoesNotMatchRegularExpression('/^\s*load_charts\(false\);/m', $html);
        $this->assertStringContainsString("if (!window.Chart) {", $html);
        $this->assertDoesNotMatchRegularExpression('/^\s*moment\.locale\(/m', $html);
    }
}
