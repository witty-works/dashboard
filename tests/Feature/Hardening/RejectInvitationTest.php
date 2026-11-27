<?php

namespace Tests\Feature\Hardening;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rejecting an invitation deletes it, so it is a POST with a CSRF token, not
 * a link another site could open, and it needs the invited address to be
 * verified: an account that only claims the address must not see or delete
 * invitations meant for it.
 */
class RejectInvitationTest extends TestCase
{
    use RefreshDatabase;

    private function invitation(string $email)
    {
        $owner = User::factory()->withPersonalTeam()->create();

        return $owner->ownedTeams()->firstOrFail()->teamInvitations()->create(['email' => $email, 'role' => 'editor']);
    }

    public function test_rejecting_is_not_a_get(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $invitation = $this->invitation($user->email);

        $response = $this->actingAs($user)->withoutLocaleRedirects()
            ->get(route('team-invitations.reject', $invitation));

        // 405, or 404 where the fallback route answers first: not handled.
        $this->assertContains($response->status(), [404, 405]);

        $this->assertModelExists($invitation);
    }

    public function test_the_invited_person_rejects_it(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $invitation = $this->invitation($user->email);

        $this->actingAs($user)->withoutLocaleRedirects()
            ->post(route('team-invitations.reject', $invitation))
            ->assertRedirect();

        $this->assertModelMissing($invitation);
    }

    public function test_an_unverified_address_cannot_reject_or_see_it(): void
    {
        $user = User::factory()->withPersonalTeam()->unverified()->create();
        $invitation = $this->invitation($user->email);

        $this->actingAs($user)->withoutLocaleRedirects()
            ->post(route('team-invitations.reject', $invitation))
            ->assertForbidden();

        $this->assertModelExists($invitation);
        $this->assertCount(0, $user->invitations);
    }
}
