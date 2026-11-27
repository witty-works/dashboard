<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * The analytics page's charts have an HTML legend and a data table each
 * (filled by its script, checked in a browser), the refresh line is not a
 * live region, and its colours reach 3:1 (#986).
 */
class AnalyticsAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    private function page(): string
    {
        Bus::fake();
        config(['posthog.enabled' => true, 'posthog.analytics_pages' => true]);
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->firstOrFail();
        $user->switchTeam($team);
        $user->forceFill(['license_team_id' => $team->id])->save();

        return $this->actingAs($user->refresh())->withoutLocaleRedirects()->get('/user/analytics')->assertOk()->getContent();
    }

    public function test_every_chart_has_a_place_for_its_data_table(): void
    {
        $html = $this->page();

        foreach (['eventsChart', 'topSubCategoriesBarChart', 'topSubCategoriesChart', 'topWordsChart', 'topWordsChartCorporateRules'] as $chart) {
            $this->assertMatchesRegularExpression('/<details id="' . $chart . '-data" class="chart-data" hidden>\s*<summary[^>]*>' . preg_quote(__('content.chart_data_table'), '/') . '<\/summary>/', $html);
        }
    }

    public function test_line_charts_have_an_html_legend(): void
    {
        $html = $this->page();

        foreach (['eventsChart', 'topSubCategoriesChart'] as $chart) {
            $this->assertStringContainsString('id="' . $chart . '-legend" class="chart-legend lato-small-text-p" role="group"', $html);
            $this->assertStringContainsString("legend: {display: false}", $html);
        }
        $this->assertStringNotContainsString('generateLabels', $html);
    }

    public function test_the_refresh_line_is_not_a_live_region_and_keeps_its_button(): void
    {
        $html = $this->page();

        $this->assertMatchesRegularExpression('/<span id="lastRefresh"(?![^>]*role=)[^>]*>/', $html);
        $this->assertStringContainsString('<button type="button" class="button primary-button-red" id="refresh-data" hidden>', $html);
        $this->assertStringNotContainsString("innerHTML += '&nbsp; &nbsp; <button", $html);
    }

    public function test_the_chart_colours_reach_three_to_one_on_white(): void
    {
        $html = $this->page();
        preg_match('/const colors = \[(.*?)\];/s', $html, $match);
        preg_match_all("/'#([0-9a-f]{6})'/", $match[1], $colours);

        $this->assertNotEmpty($colours[1]);
        foreach ($colours[1] as $hex) {
            $this->assertGreaterThanOrEqual(3.0, $this->contrastOnWhite($hex), "#$hex");
        }
    }

    private function contrastOnWhite(string $hex): float
    {
        $channel = function (int $value): float {
            $c = $value / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        [$r, $g, $b] = array_map(fn ($i) => $channel(hexdec(substr($hex, $i, 2))), [0, 2, 4]);
        $luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

        return 1.05 / ($luminance + 0.05);
    }
}
