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
        }
        // Both line charts switch their canvas legend off.
        $this->assertSame(2, substr_count($html, 'legend: {display: false}'));
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

    public function test_the_tab_underline_reaches_three_to_one_on_the_page_background(): void
    {
        preg_match('/\.analytics-tab-line \{[^}]*border-bottom: 3px solid #([0-9a-f]{6})/', file_get_contents(resource_path('css/app.css')), $match);

        $this->assertGreaterThanOrEqual(3.0, $this->contrast($match[1], 'f5f5f5'));
    }

    public function test_a_hidden_button_stays_hidden(): void
    {
        // .button sets display: flex after Tailwind's [hidden] rule.
        $this->assertMatchesRegularExpression('/\.button\[hidden\]\s*\{\s*display:\s*none;/', file_get_contents(resource_path('css/app.css')));
    }

    private function contrastOnWhite(string $hex): float
    {
        return $this->contrast($hex, 'ffffff');
    }

    private function contrast(string $hex, string $background): float
    {
        $lum = function (string $h): float {
            $channel = fn (int $v): float => ($c = $v / 255) <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
            [$r, $g, $b] = array_map(fn ($i) => $channel(hexdec(substr($h, $i, 2))), [0, 2, 4]);

            return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
        };
        [$a, $b] = [$lum($hex), $lum($background)];

        return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
    }

}
