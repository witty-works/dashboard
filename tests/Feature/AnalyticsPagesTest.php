<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * analytics.blade.php carried the densest plan gating in the application — a
 * locked time-range picker, an is_premium_user flag threaded through the chart
 * JavaScript, and "upgrade" labels on the check-highlights series. All of that
 * came out with billing, and the controller no longer passes is_premium_user, so
 * these render the page to prove nothing is left referencing it.
 */
class AnalyticsPagesTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    public function test_personal_analytics_page_renders(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/user/analytics')
            ->assertOk()
            ->assertViewIs('analytics');
    }

    public function test_team_analytics_page_renders_for_a_team_owner(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/team/analytics')
            ->assertOk()
            ->assertViewIs('analytics');
    }

    /** Every time range is selectable now that the plan restriction is gone. */
    public function test_all_time_ranges_are_offered(): void
    {
        $response = $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/user/analytics')
            ->assertOk();

        foreach (['1m', '3m', '1y'] as $range) {
            $response->assertSee($range, false);
        }
    }

    /**
     * teams.user_access_to_team_analytics defaults to 1, so ordinary members can
     * see team analytics unless an admin turns it off. Previously a non-premium
     * team had that flag forced on and could not turn it off at all; with billing
     * gone the team's own setting is what counts, so both sides are asserted.
     */
    public function test_team_analytics_follows_the_team_access_setting(): void
    {
        $owner = $this->signedInUser();
        $team = $owner->currentTeam;

        $member = User::factory()->create();
        $team->users()->attach($member, ['role' => 'employee']);
        $member->switchTeam($team);
        $member = $member->refresh();

        $this->actingAs($member)
            ->withoutLocaleRedirects()
            ->get('/team/analytics')
            ->assertOk();

        $team->forceFill(['user_access_to_team_analytics' => false])->save();

        $this->actingAs($member->refresh())
            ->withoutLocaleRedirects()
            ->get('/team/analytics')
            ->assertForbidden();
    }
}
