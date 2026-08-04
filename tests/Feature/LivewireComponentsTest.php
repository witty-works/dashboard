<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Mounts a representative Livewire component from each family. Livewire's own
 * major versions and the framework upgrade both tend to break mounting first, and
 * a component that will not mount takes its whole page down.
 *
 * "user" components are bound to the User model, "organization"/"teams"
 * components to the Team.
 */
class LivewireComponentsTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    #[DataProvider('userScopedComponentProvider')]
    public function test_user_scoped_component_mounts(string $component): void
    {
        $user = $this->signedInUser();
        $this->actingAs($user);

        Livewire::test($component, ['model' => $user])->assertOk();
    }

    #[DataProvider('teamScopedComponentProvider')]
    public function test_team_scoped_component_mounts(string $component): void
    {
        $user = $this->signedInUser();
        $this->actingAs($user);

        Livewire::test($component, ['model' => $user->currentTeam])->assertOk();
    }

    public function test_plan_summary_mounts(): void
    {
        $user = $this->signedInUser();
        $this->actingAs($user);

        Livewire::test(\App\Livewire\Teams\PlanSummary::class, ['team' => $user->currentTeam])
            ->assertOk();
    }

    public static function userScopedComponentProvider(): array
    {
        return [
            'language' => [\App\Livewire\UserLanguageSettings\Language::class],
            'orthography' => [\App\Livewire\UserLanguageSettings\Orthography::class],
            'german' => [\App\Livewire\UserLanguageSettings\German::class],
            'french' => [\App\Livewire\UserLanguageSettings\French::class],
            'inspirations' => [\App\Livewire\UserLanguageSettings\Inspirations::class],
            'generic masculine' => [\App\Livewire\UserLanguageSettings\GenericMasculine::class],
            'false positive: show' => [\App\Livewire\UserFalsePositive\Show::class],
            'term replacement: show' => [\App\Livewire\UserTermReplacement\Show::class],
            'domain: show' => [\App\Livewire\UserDomain\Show::class],
        ];
    }

    public static function teamScopedComponentProvider(): array
    {
        return [
            'language' => [\App\Livewire\OrganizationLanguageSettings\Language::class],
            'orthography' => [\App\Livewire\OrganizationLanguageSettings\Orthography::class],
            'german' => [\App\Livewire\OrganizationLanguageSettings\German::class],
            'false positive: show' => [\App\Livewire\OrganizationFalsePositive\Show::class],
            'term replacement: show' => [\App\Livewire\OrganizationTermReplacement\Show::class],
            'domain: show' => [\App\Livewire\OrganizationDomain\Show::class],
            'store context' => [\App\Livewire\Teams\StoreContext::class],
        ];
    }
}
