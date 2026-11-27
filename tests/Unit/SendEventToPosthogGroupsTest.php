<?php

namespace Tests\Unit;

use App\Jobs\SendEventToPosthog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PostHog links an event to a group through its "$groups" property; with
 * "$group" the dashboard's events were not linked to the team.
 */
class SendEventToPosthogGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_event_is_linked_to_its_team(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->firstOrFail();

        $job = new SendEventToPosthog($user, 'some event', [], false, $team);
        $properties = (fn () => $this->properties)->call($job);

        $this->assertArrayHasKey('$groups', $properties);
        $this->assertArrayNotHasKey('$group', $properties);
    }
}
