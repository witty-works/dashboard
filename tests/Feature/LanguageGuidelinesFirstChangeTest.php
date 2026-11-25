<?php

namespace Tests\Feature;

use App\Models\LanguageGuidelines;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A user or team gets its language guidelines row only when a setting is
 * first saved. The first change must land all the same: the extension's
 * "show less" and the orthography switch update the row in place.
 */
class LanguageGuidelinesFirstChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_switching_off_spelling_and_grammar_sticks_for_a_user_without_guidelines(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());
        $this->assertNull(LanguageGuidelines::where('user_id', $user->id)->first());
        $this->actingAs($user);

        Livewire::test(\App\Livewire\UserLanguageSettings\Orthography::class, ['model' => $user])
            ->set('orthography', false)
            ->call('updateLanguageGuidelinesOrthography')
            ->assertOk();

        $stored = LanguageGuidelines::where('user_id', $user->id)->firstOrFail();
        $this->assertContains('orthography', $stored->disabled_categories);
    }

    /** A stored row whose list is NULL: in memory it reads as [] already. */
    private function rowWithNullForceList(): LanguageGuidelines
    {
        $user = User::factory()->withPersonalTeam()->create();
        $row = LanguageGuidelines::getLanguageGuidelines($user);
        $row->save();
        DB::table('language_guidelines')->where('id', $row->id)->update(['disabled_categories_force' => null]);

        return LanguageGuidelines::getLanguageGuidelines($user);
    }

    public function test_adding_to_a_list_that_is_null_in_the_database(): void
    {
        $guidelines = $this->rowWithNullForceList();

        $guidelines->inPlaceUpateArray('orthography', 'disabled_categories_force', false);

        $this->assertSame(['orthography'], $guidelines->fresh()->disabled_categories_force);
    }

    public function test_removing_from_a_list_that_is_null_in_the_database(): void
    {
        $guidelines = $this->rowWithNullForceList();

        $guidelines->inPlaceUpateArray('orthography', 'disabled_categories_force', true);

        $this->assertSame([], $guidelines->fresh()->disabled_categories_force);
    }
}
