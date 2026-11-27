<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\TeamInvitation;
use Tests\TestCase;

/**
 * The team members page: each listed member and invitation is keyed for
 * Livewire, and a member's label says whose team settings apply to them,
 * which is what the former licence now means.
 */
class TeamMemberListTest extends TestCase
{
    use RefreshDatabase;

    private function page(): array
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->ownedTeams()->firstOrFail();
        $owner->switchTeam($team);
        $owner->forceFill(['license_team_id' => $team->id])->save();
        $member = User::factory()->create();
        $team->users()->attach($member, ['role' => 'user']);
        $member->forceFill(['license_team_id' => $team->id])->save();
        $invitation = $team->teamInvitations()->create(['email' => 'invited@example.org', 'role' => 'user']);

        $html = $this->actingAs($owner->refresh())->withoutLocaleRedirects()->get("/team/show")->assertOk()->getContent();

        return [$html, $member, $invitation];
    }

    public function test_members_and_invitations_are_keyed(): void
    {
        [$html, $member, $invitation] = $this->page();

        $this->assertStringContainsString('wire:key="member-' . $member->id . '"', $html);
        $this->assertStringContainsString('wire:key="invitation-' . $invitation->id . '"', $html);
    }

    public function test_a_member_label_says_whose_settings_apply(): void
    {
        [$html] = $this->page();

        $this->assertStringContainsString(e(__('teams.active_license')), $html);
        $this->assertStringNotContainsString('Licensed', $html);
    }
}
