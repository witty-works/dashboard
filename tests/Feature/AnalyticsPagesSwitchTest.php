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

    /** Routes are registered at boot, so the app boots again with the setting. */
    private function boot(bool $posthog, bool $pages): void
    {
        $env = ['POSTHOG_ENABLED' => $posthog ? 'true' : 'false', 'ANALYTICS_PAGES_ENABLED' => $pages ? 'true' : 'false'];
        $before = [];
        foreach ($env as $name => $value) {
            $before[$name] = [getenv($name), $_ENV[$name] ?? null, $_SERVER[$name] ?? null];
            putenv("$name=$value");
            $_ENV[$name] = $_SERVER[$name] = $value;
        }
        $this->refreshApplication();
        // The new app has its own connection: roll back what this test writes
        // there too, as RefreshDatabase does for the first one.
        $this->beginDatabaseTransaction();
        $this->withoutVite();
        // Back to phpunit.xml's values for the tests after this one.
        foreach ($before as $name => [$getenv, $envValue, $serverValue]) {
            $getenv === false ? putenv($name) : putenv("$name=$getenv");
            if ($envValue === null) { unset($_ENV[$name]); } else { $_ENV[$name] = $envValue; }
            if ($serverValue === null) { unset($_SERVER[$name]); } else { $_SERVER[$name] = $serverValue; }
        }
    }

    public function test_hidden_pages_are_gone_while_collection_stays(): void
    {
        $this->boot(posthog: true, pages: false);
        $owner = $this->owner();
        $this->actingAs($owner)->withoutLocaleRedirects();

        $this->get('/user/analytics')->assertNotFound();
        $this->get('/team/analytics')->assertNotFound();
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
