<?php

namespace Tests\Feature;

use App\Jobs\SyncOrganizationToNlpApi;
use App\Livewire\Teams\ConfirmBeforeChecking;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The organisation setting "Ask before checking each field": saved by a team
 * admin, and sent to the NLP API forced whenever it is on, so members' browser
 * extensions ask before sending any text.
 */
class ConfirmBeforeCheckingTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function syncedSetting(Team $team): array
    {
        return (new SyncOrganizationToNlpApi($team))->getData($team->refresh())['config']['confirm_before_checking'];
    }

    public function test_it_is_off_for_a_new_team(): void
    {
        $team = $this->signedInUser()->currentTeam;

        $this->assertFalse((bool) $team->confirm_before_checking);
    }

    public function test_the_privacy_settings_page_shows_the_setting(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/team/language/privacy-settings')
            ->assertOk()
            ->assertSee('id="confirm_before_checking"', escape: false)
            ->assertSee(__('teams.confirm_before_checking_description'));
    }

    public function test_the_team_owner_can_switch_it_on_and_off(): void
    {
        $user = $this->signedInUser();
        $team = $user->currentTeam;

        Livewire::actingAs($user)
            ->test(ConfirmBeforeChecking::class, ['model' => $team])
            ->set('confirm_before_checking', true)
            ->call('updateTeamsConfirmBeforeChecking')
            ->assertHasNoErrors()
            ->assertDispatched('saved');

        $this->assertTrue((bool) $team->refresh()->confirm_before_checking);

        Livewire::actingAs($user)
            ->test(ConfirmBeforeChecking::class, ['model' => $team])
            ->assertSet('confirm_before_checking', true)
            ->set('confirm_before_checking', false)
            ->call('updateTeamsConfirmBeforeChecking');

        $this->assertFalse((bool) $team->refresh()->confirm_before_checking);
    }

    public function test_a_member_without_the_update_permission_cannot_change_it(): void
    {
        $owner = $this->signedInUser();
        $team = $owner->currentTeam;

        $member = User::factory()->create();
        $team->users()->attach($member, ['role' => 'user']);

        Livewire::actingAs($member)
            ->test(ConfirmBeforeChecking::class, ['model' => $team])
            ->set('confirm_before_checking', true)
            ->call('updateTeamsConfirmBeforeChecking')
            ->assertForbidden();

        $this->assertFalse((bool) $team->refresh()->confirm_before_checking);
    }

    public function test_a_switched_on_setting_is_synced_as_forced(): void
    {
        $team = $this->signedInUser()->currentTeam;
        $team->confirm_before_checking = true;
        $team->save();

        $this->assertSame(['value' => true, 'status' => 'force'], $this->syncedSetting($team));
    }

    public function test_a_switched_off_setting_is_synced_as_a_suggestion(): void
    {
        $team = $this->signedInUser()->currentTeam;

        $this->assertSame(['value' => false, 'status' => 'suggestion'], $this->syncedSetting($team));
    }

    public function test_the_texts_exist_in_every_locale(): void
    {
        foreach (['en', 'de', 'fr'] as $locale) {
            foreach (['confirm_before_checking_headline', 'confirm_before_checking', 'confirm_before_checking_description'] as $key) {
                $this->assertNotSame("teams.$key", __("teams.$key", [], $locale), "teams.$key is missing for $locale");
            }
        }
    }
}
