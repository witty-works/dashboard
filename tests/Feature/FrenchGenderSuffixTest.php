<?php

namespace Tests\Feature;

use App\Jobs\SendEventToPosthog;
use App\Jobs\SyncOrganizationToNlpApi;
use App\Jobs\SyncUserToNlpApi;
use App\Livewire\OrganizationLanguageSettings\French as TeamFrench;
use App\Livewire\UserLanguageSettings\French as UserFrench;
use App\Models\LanguageGuidelines;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The French feminine suffix (`french_gender_suffix`): short, as in
 * utilisateur·rice, or full, as in utilisateur·trice. Set next to the French
 * separator, locked with it, and sent to the NLP API with it.
 */
class FrenchGenderSuffixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    public function test_it_is_short_by_default(): void
    {
        $team = $this->signedInUser()->currentTeam;

        $this->assertSame('short', LanguageGuidelines::getLanguageGuidelines($team)->french_gender_suffix);
    }

    public function test_a_team_admin_sets_it_and_the_team_config_carries_it(): void
    {
        $user = $this->signedInUser();
        $team = $user->currentTeam;

        Livewire::actingAs($user)
            ->test(TeamFrench::class, ['model' => $team])
            ->set('french_gender_suffix', 'full')
            ->set('french_rules_force', true)
            ->call('updateLanguageGuidelinesFrench')
            ->assertHasNoErrors();

        $this->assertSame('full', LanguageGuidelines::getLanguageGuidelines($team)->french_gender_suffix);
        $this->assertSame(
            ['value' => 'full', 'status' => 'force'],
            (new SyncOrganizationToNlpApi($team))->getData($team->refresh())['config']['french_gender_suffix']
        );
    }

    public function test_a_user_sets_their_own_and_their_config_carries_it(): void
    {
        $user = $this->signedInUser();
        $guidelines = LanguageGuidelines::getLanguageGuidelines($user->currentTeam);
        $guidelines->french_rules_force = false;
        $guidelines->save();

        Livewire::actingAs($user)
            ->test(UserFrench::class, ['model' => $user])
            ->set('french_gender_suffix', 'full')
            ->call('updateLanguageGuidelinesFrench')
            ->assertHasNoErrors();

        $this->assertSame('full', LanguageGuidelines::getLanguageGuidelines($user)->french_gender_suffix);
        $this->assertSame(
            'full',
            (new SyncUserToNlpApi($user))->getData($user->refresh())['config']['french_gender_suffix']['value']
        );
    }

    public function test_a_user_sees_the_teams_choice_while_the_team_locks_french(): void
    {
        $user = $this->signedInUser();
        $guidelines = LanguageGuidelines::getLanguageGuidelines($user->currentTeam);
        $guidelines->french_gender_suffix = 'full';
        $guidelines->french_rules_force = true;
        $guidelines->save();

        Livewire::actingAs($user)
            ->test(UserFrench::class, ['model' => $user])
            ->assertSet('french_gender_suffix', 'full');
    }

    public function test_a_user_cannot_save_while_the_team_locks_french(): void
    {
        $user = $this->signedInUser();
        $guidelines = LanguageGuidelines::getLanguageGuidelines($user->currentTeam);
        $guidelines->french_rules_force = true;
        $guidelines->save();

        Livewire::actingAs($user)
            ->test(UserFrench::class, ['model' => $user])
            ->set('french_gender_suffix', 'full')
            ->call('updateLanguageGuidelinesFrench')
            ->assertForbidden();

        $this->assertSame('short', LanguageGuidelines::getLanguageGuidelines($user)->french_gender_suffix);
    }

    public function test_an_unlocked_team_suggests_its_choice(): void
    {
        $user = $this->signedInUser();
        $team = $user->currentTeam;

        Livewire::actingAs($user)
            ->test(TeamFrench::class, ['model' => $team])
            ->set('french_gender_suffix', 'full')
            ->set('french_rules_force', false)
            ->call('updateLanguageGuidelinesFrench')
            ->assertHasNoErrors();

        $this->assertSame(
            ['value' => 'full', 'status' => 'suggestion'],
            (new SyncOrganizationToNlpApi($team))->getData($team->refresh())['config']['french_gender_suffix']
        );
    }

    public function test_the_posthog_event_names_the_suffix_and_the_french_lock(): void
    {
        $user = $this->signedInUser();
        $team = $user->currentTeam;
        $guidelines = LanguageGuidelines::getLanguageGuidelines($team);
        $guidelines->german_rules_force = true;
        $guidelines->save();

        Livewire::actingAs($user)
            ->test(TeamFrench::class, ['model' => $team])
            ->set('french_gender_suffix', 'full')
            ->set('french_rules_force', false)
            ->call('updateLanguageGuidelinesFrench');

        Bus::assertDispatched(SendEventToPosthog::class, function (SendEventToPosthog $job) {
            $properties = (fn () => $this->properties)->call($job);

            return array_key_exists('french_gender_separator', $properties)
                && ($properties['french_gender_suffix'] ?? null) === 'full'
                && $properties['force'] === false;
        });
    }

    public function test_an_unknown_value_is_refused(): void
    {
        $user = $this->signedInUser();

        Livewire::actingAs($user)
            ->test(TeamFrench::class, ['model' => $user->currentTeam])
            ->set('french_gender_suffix', 'medium')
            ->call('updateLanguageGuidelinesFrench')
            ->assertHasErrors('french_gender_suffix');
    }

    public function test_the_options_file_labels_both_values(): void
    {
        $options = json_decode(file_get_contents(storage_path('app/data/config_options.json')), true);

        foreach (['en', 'de', 'fr'] as $locale) {
            $labels = $options['french_gender_suffix']['translations'][$locale];
            $this->assertStringContainsString('utilisateur·rice', $labels['short']);
            $this->assertStringContainsString('utilisateur·trice', $labels['full']);
        }
    }
}
