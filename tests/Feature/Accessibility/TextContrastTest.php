<?php

namespace Tests\Feature\Accessibility;

use App\Livewire\Teams\TeamMemberManagerHelpHero;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Text needs 4.5:1 against its background (WCAG 1.4.3). Tailwind's green-600
 * is 3.3:1 on white and red-600 is 4.4:1 on the gray-100 guest pages; the
 * -700 shades pass on both.
 */
class TextContrastTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_and_error_text_use_the_dark_shades(): void
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        foreach ($iterator as $file) {
            // The analytics page is reworked separately.
            if (! str_ends_with($file->getFilename(), '.blade.php') || $file->getFilename() === 'analytics.blade.php') {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression(
                '/\btext-(red|green)-(400|500|600)\b/',
                file_get_contents($file->getPathname()),
                $file->getPathname()
            );
        }
    }

    public function test_role_descriptions_are_not_faded(): void
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->firstOrFail();
        $user->switchTeam($team);
        $this->actingAs($user->refresh());

        $html = Livewire::test(TeamMemberManagerHelpHero::class, ['team' => $team])
            ->set('currentlyManagingRole', true)
            ->html();

        // Faded to half, gray-600 text drops to about 3.9:1.
        $this->assertStringContainsString(__('content.admin_role'), $html);
        $this->assertStringNotContainsString('opacity-50', $html);
    }

    public function test_the_role_dialog_says_which_role_is_chosen(): void
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->firstOrFail();
        $user->switchTeam($team);
        $this->actingAs($user->refresh());

        $html = Livewire::test(TeamMemberManagerHelpHero::class, ['team' => $team])
            ->set('currentlyManagingRole', true)
            ->set('currentRole', 'admin')
            ->html();

        // Without the fading, the check mark and aria-pressed carry the choice.
        $this->assertMatchesRegularExpression('/<button[^>]*aria-pressed="true"[^>]*wire:click="\$set\(\'currentRole\', \'admin\'\)"/', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*aria-pressed="false"[^>]*wire:click="\$set\(\'currentRole\', \'user\'\)"/', $html);
    }
}
