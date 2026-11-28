<?php

namespace Tests\Feature\Hardening;

use App\Actions\Jetstream\CreateTeam;
use App\Mail\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Team names and addresses go into translated text that is printed as HTML,
 * so they are escaped first. The name mutators strip tags, but escaping is
 * what makes the output safe whatever a name holds.
 */
class EscapedNamesTest extends TestCase
{
    use RefreshDatabase;

    private const NAME = 'R&D "Team"';

    private function ownerAndInvitation(string $email): array
    {
        $owner = User::factory()->withPersonalTeam()->create(['email' => 'o&wner@example.org']);
        $team = $owner->ownedTeams()->firstOrFail();
        $team->forceFill(['name' => self::NAME])->save();

        return [$owner, $team, $team->teamInvitations()->create(['email' => $email, 'role' => 'editor'])];
    }

    public function test_the_invitation_banner_escapes_the_team_and_owner(): void
    {
        $user = User::factory()->create();
        $this->ownerAndInvitation($user->email);

        $this->actingAs($user)->withoutLocaleRedirects()->get(route('no-team'))
            ->assertSee('R&amp;D &quot;Team&quot;', false)
            ->assertSee('o&amp;wner@example.org', false);
    }

    public function test_the_invitation_mail_escapes_the_team_and_owner(): void
    {
        [$owner, , $invitation] = $this->ownerAndInvitation('invitee@example.org');
        $this->actingAs($owner);

        $html = (new TeamInvitation($invitation))->render();

        $this->assertStringContainsString('R&amp;D', $html);
        $this->assertStringNotContainsString('R&D', $html);
    }

    public function test_a_new_team_name_cannot_hold_markup(): void
    {
        // Someone in no team yet may create one.
        $user = User::factory()->create();

        $this->expectException(ValidationException::class);
        app(CreateTeam::class)->create($user, ['name' => '<b>Team</b>']);
    }
}
