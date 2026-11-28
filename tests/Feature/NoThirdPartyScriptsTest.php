<?php

namespace Tests\Feature;

use App\Livewire\UserFalsePositive\Show as UserFalsePositives;
use App\Models\FalsePositive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The dashboard loads no Google Tag Manager, HubSpot, HelpHero or PostHog
 * browser script, so it needs no cookie banner. The old switches are set
 * here so that the scripts would render if they were still there.
 */
class NoThirdPartyScriptsTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPTS = ['googletagmanager', '_hsp', 'posthog.init', 'app.helphero.co', 'HelpHero.'];

    protected function setUp(): void
    {
        parent::setUp();
        config(['posthog.js_enabled' => true, 'helphero.js_enabled' => true, 'helphero.app_id' => 'x']);
    }

    private function assertNoThirdPartyScripts($response): void
    {
        $response->assertOk();
        foreach (self::SCRIPTS as $script) {
            $this->assertStringNotContainsString($script, $response->getContent(), "the page loads $script");
        }
    }

    public function test_the_guest_layout_loads_no_third_party_scripts(): void
    {
        $this->assertNoThirdPartyScripts($this->get('/login'));
    }

    public function test_the_app_layout_loads_no_third_party_scripts(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        $this->assertNoThirdPartyScripts($this->actingAs($user->refresh())->get('/en/user/profile'));
    }

    public function test_the_plain_layout_loads_no_third_party_scripts(): void
    {
        // The account deletion page; the Office sign-in pages never loaded them.
        $user = User::factory()->withPersonalTeam()->create();
        $url = URL::temporarySignedRoute('account.delete.confirm', now()->addMinutes(30), ['user' => $user->id]);

        $this->assertNoThirdPartyScripts($this->get($url));
    }

    public function test_changing_a_setting_sends_no_helphero_update(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $entry = FalsePositive::forceCreate(['user_id' => $user->id, 'false_positive' => 'word']);

        Livewire::actingAs($user)
            ->test(UserFalsePositives::class, ['model' => $user])
            ->call('deleteFalsePositive', $entry->id)
            ->assertNotDispatched('helpHeroUpdate');

        $this->assertModelMissing($entry);
    }
}
