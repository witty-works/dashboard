<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The analytics page's script: translations go into JavaScript as JSON, the
 * number of categories is not written into the code, and the script is part
 * of the page rather than trailing after it.
 */
class AnalyticsScriptPlacementTest extends TestCase
{
    use RefreshDatabase;

    private function page(): string
    {
        config(['posthog.enabled' => true, 'posthog.analytics_pages' => true]);
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $this->actingAs($user->refresh())->withoutLocaleRedirects()->get('/user/analytics')->assertOk()->getContent();
    }

    public function test_the_script_is_inside_the_page(): void
    {
        $html = $this->page();

        $this->assertSame('', trim(substr($html, strrpos($html, '</html>') + strlen('</html>'))), 'Nothing may follow </html>.');
    }

    public function test_translations_reach_the_script_as_json(): void
    {
        $source = file_get_contents(resource_path('views/analytics.blade.php'));
        $script = substr($source, strpos($source, '<script @cspNonce>'));

        // A string in the script; an HTML attribute the script writes
        // (aria-label="{{ ... }}") is right with {{ }} escaping.
        $this->assertDoesNotMatchRegularExpression('/(?<!=)[\'"`]\{\{ __\(/', $script, 'Use @json(__(...)), not "{{ __(...) }}" inside a string.');
        $this->assertStringNotContainsString('numberOfChecked === 6', $script);
    }
}
