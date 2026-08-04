<?php

namespace Tests\Feature;

use App\Livewire\Prompt;
use App\Livewire\Teams\LlmAlternatives;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * app.llm_enabled is the installation-wide switch for Witty GPT, for deployments
 * whose backend has no LLM support. It has to hold on the server too, not just in
 * the navigation, because Livewire components stay reachable over their own
 * endpoint even when the page route is gone.
 */
class WittyGptToggleTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    public function test_prompt_route_is_registered_when_enabled(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByName('prompt'));
    }

    public function test_prompt_page_renders_when_enabled(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/prompt')
            ->assertOk()
            ->assertViewIs('prompt');
    }

    public function test_navigation_links_to_witty_gpt_when_enabled(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/user/profile')
            ->assertOk()
            ->assertSee('svg/navigationIcons/prompt.svg', false);
    }

    public function test_sending_a_prompt_is_rejected_when_disabled(): void
    {
        config(['app.llm_enabled' => false]);

        Livewire::actingAs($this->signedInUser())
            ->test(Prompt::class)
            ->set('prompt', 'anything at all')
            ->call('sendPrompt')
            ->assertHasErrors('prompt');
    }

    public function test_team_toggle_cannot_be_switched_on_when_disabled(): void
    {
        config(['app.llm_enabled' => false]);

        $user = $this->signedInUser();
        $team = $user->currentTeam;

        Livewire::actingAs($user)
            ->test(LlmAlternatives::class, ['model' => $team])
            ->set('llm_alternatives', true)
            ->call('updateTeamsLlmAlternatives');

        $this->assertFalse((bool) $team->refresh()->llm_alternatives);
    }

    public function test_navigation_hides_witty_gpt_when_disabled(): void
    {
        config(['app.llm_enabled' => false]);

        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/user/profile')
            ->assertOk()
            ->assertDontSee('svg/navigationIcons/prompt.svg', false);
    }
}
