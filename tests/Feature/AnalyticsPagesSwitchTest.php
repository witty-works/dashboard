<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * The analytics pages show only when PostHog collects events and the
 * deployment wants them shown (ANALYTICS_PAGES_ENABLED); a deployment can
 * collect events before its pages have anything worth showing.
 */
class AnalyticsPagesSwitchTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->firstOrFail();
        $user->switchTeam($team);
        // The personal settings pages are for people licensed to a team.
        $user->forceFill(['license_team_id' => $team->id])->save();

        return $user->refresh();
    }

    private function boot(bool $posthog, bool $pages): void
    {
        config(['posthog.enabled' => $posthog, 'posthog.analytics_pages' => $pages]);
    }

    public function test_hidden_pages_are_gone_while_collection_stays(): void
    {
        $this->boot(posthog: true, pages: false);
        $owner = $this->owner();
        $this->actingAs($owner)->withoutLocaleRedirects();

        $this->get('/user/analytics')->assertNotFound();
        $this->get('/team/analytics')->assertNotFound();
        $this->getJson('/api/user/analytics')->assertNotFound();
        $this->getJson('/api/team/analytics')->assertNotFound();
        $this->get('/user/profile')->assertDontSee('/user/analytics')->assertDontSee('/team/analytics');
        // Who may see the team's analytics is moot; the personal consent to
        // being counted still matters while PostHog collects.
        $this->get('/team/language/privacy-settings')->assertDontSee('wire:name="teams.analytics-user-access"', false);
        $this->get('/user/language/privacy-settings')->assertSee('wire:name="team-analytics"', false);
    }

    public function test_without_posthog_neither_setting_shows(): void
    {
        $this->boot(posthog: false, pages: true);
        $this->actingAs($this->owner())->withoutLocaleRedirects();

        $this->get('/user/language/privacy-settings')->assertDontSee('wire:name="team-analytics"', false);
        $this->get('/team/language/privacy-settings')->assertDontSee('wire:name="teams.analytics-user-access"', false);
    }

    public function test_shown_pages_have_their_menu_entry_and_settings(): void
    {
        $this->boot(posthog: true, pages: true);
        $this->actingAs($this->owner())->withoutLocaleRedirects();

        $this->get('/team/language/privacy-settings')->assertSee('wire:name="teams.analytics-user-access"', false);
        $this->get('/user/profile')->assertSee('/user/analytics', false);
    }
}
