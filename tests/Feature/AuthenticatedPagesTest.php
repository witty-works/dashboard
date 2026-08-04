<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Renders the main signed-in pages. These exercise Blade, Livewire, Jetstream and
 * the policy layer together, which is where a framework upgrade is most likely to
 * surface.
 *
 * Each case asserts the rendered view name as well as the status, so a test
 * cannot pass against the fallback error page.
 */
class AuthenticatedPagesTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    #[DataProvider('pageProvider')]
    public function test_page_renders_for_an_authenticated_user(string $uri, string $view): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get($uri)
            ->assertOk()
            ->assertViewIs($view);
    }

    public static function pageProvider(): array
    {
        return [
            'prompt' => ['/prompt', 'prompt'],
            'user profile' => ['/user/profile', 'profile.show'],
            'academy' => ['/user/academy', 'academy'],
            'team show' => ['/team/show', 'teams.show'],
            'team language guidelines' => ['/team/language/dictionary', 'teams.language-guidelines'],
            'word add-in' => ['/word-addin', 'word-addin'],
            'terms' => ['/terms', 'terms'],
        ];
    }

    /**
     * A user who has not completed onboarding is steered into the language setup
     * rather than the dashboard. Pins that entry-point behaviour.
     */
    public function test_root_sends_a_new_user_into_language_onboarding(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/')
            ->assertRedirectContains('/user/language');
    }

    public function test_personal_language_pages_require_completed_onboarding(): void
    {
        $this->actingAs($this->signedInUser())
            ->withoutLocaleRedirects()
            ->get('/user/language/dictionary')
            ->assertRedirectContains('/user/profile');
    }
}
