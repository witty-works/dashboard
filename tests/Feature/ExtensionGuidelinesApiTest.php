<?php

namespace Tests\Feature;

use App\Jobs\SyncUserToNlpApi;
use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\LanguageGuidelines;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * The API the browser extension writes the user's guidelines with
 * (`/api/user/language/*`, `auth:extension`): "ignore permanently", the
 * per-site switch and "show less of this". Each call changes only the
 * token's own user, and a change is synced to the NLP API.
 */
class ExtensionGuidelinesApiTest extends TestCase
{
    use RefreshDatabase;

    private function asExtension(?User $user = null): User
    {
        $user ??= User::factory()->withPersonalTeam()->create();
        Passport::actingAs($user, [], 'extension');
        // Creating users dispatches syncs of its own; count only the API's.
        Bus::fake([SyncUserToNlpApi::class]);

        return $user;
    }

    public function test_every_route_needs_a_token(): void
    {
        Bus::fake([SyncUserToNlpApi::class]);
        $this->putJson('/api/user/language/ignore-words?false_positive=guys')->assertUnauthorized();
        $this->deleteJson('/api/user/language/ignore-words?false_positive=guys')->assertUnauthorized();
        $this->putJson('/api/user/language/domains?domain=example.com')->assertUnauthorized();
        $this->deleteJson('/api/user/language/domains?domain=example.com')->assertUnauthorized();
        $this->putJson('/api/user/language/customize-witty?diversity_dimension=age')->assertUnauthorized();
        $this->assertSame(0, FalsePositive::count() + Domain::count());
    }

    public function test_ignoring_a_word_stores_it_for_the_tokens_user_and_syncs(): void
    {
        $user = $this->asExtension();

        $this->putJson('/api/user/language/ignore-words?false_positive=guys')->assertNoContent();

        $this->assertDatabaseHas('false_positives', ['user_id' => $user->id, 'false_positive' => 'guys', 'team_id' => null]);
        Bus::assertDispatched(SyncUserToNlpApi::class);
    }

    public function test_ignoring_a_word_twice_stores_it_once_and_syncs_once(): void
    {
        $user = $this->asExtension();

        $this->putJson('/api/user/language/ignore-words?false_positive=guys')->assertNoContent();
        $this->putJson('/api/user/language/ignore-words?false_positive=guys')->assertNoContent();

        $this->assertSame(1, FalsePositive::where('user_id', $user->id)->count());
        Bus::assertDispatchedTimes(SyncUserToNlpApi::class, 1);
    }

    public function test_an_empty_or_overlong_word_is_refused(): void
    {
        $this->asExtension();

        $this->putJson('/api/user/language/ignore-words?false_positive=')->assertStatus(400);
        $this->putJson('/api/user/language/ignore-words?false_positive=' . str_repeat('a', 251))->assertStatus(400);
        $this->deleteJson('/api/user/language/ignore-words?false_positive=')->assertStatus(400);
        $this->assertSame(0, FalsePositive::count());
        Bus::assertNotDispatched(SyncUserToNlpApi::class);
    }

    public function test_un_ignoring_removes_only_the_tokens_users_word(): void
    {
        $other = User::factory()->withPersonalTeam()->create();
        $theirs = FalsePositive::forceCreate(['user_id' => $other->id, 'false_positive' => 'guys']);
        $teams = FalsePositive::forceCreate(['team_id' => $other->currentTeam?->id ?? $other->ownedTeams()->first()->id, 'false_positive' => 'guys']);
        $user = $this->asExtension();
        $mine = FalsePositive::forceCreate(['user_id' => $user->id, 'false_positive' => 'guys']);

        $this->deleteJson('/api/user/language/ignore-words?false_positive=guys')->assertNoContent();

        $this->assertModelMissing($mine);
        $this->assertModelExists($theirs);
        $this->assertModelExists($teams);
        Bus::assertDispatched(SyncUserToNlpApi::class);
    }

    public function test_switching_a_site_off_stores_its_domain(): void
    {
        $user = $this->asExtension();

        $this->putJson('/api/user/language/domains?domain=' . urlencode('https://mail.example.com/inbox'))->assertNoContent();

        $this->assertDatabaseHas('domains', ['user_id' => $user->id, 'domain' => 'example.com']);
        Bus::assertDispatched(SyncUserToNlpApi::class);
    }

    public function test_switching_a_site_on_again_removes_only_the_tokens_users_domain(): void
    {
        $other = User::factory()->withPersonalTeam()->create();
        $theirs = Domain::forceCreate(['user_id' => $other->id, 'domain' => 'example.com']);
        $user = $this->asExtension();
        $mine = Domain::forceCreate(['user_id' => $user->id, 'domain' => 'example.com']);

        $this->deleteJson('/api/user/language/domains?domain=example.com')->assertNoContent();

        $this->assertModelMissing($mine);
        $this->assertModelExists($theirs);
    }

    public function test_showing_less_of_a_dimension_disables_it_for_the_user(): void
    {
        $user = $this->asExtension();
        // The user has never saved a language setting: no row yet.
        $this->assertNull(LanguageGuidelines::where('user_id', $user->id)->first());

        $this->putJson('/api/user/language/customize-witty?diversity_dimension=age&direction=down')->assertNoContent();

        $disabled = (array) LanguageGuidelines::getLanguageGuidelines($user->refresh())->disabled_categories;
        $this->assertContains('age', $disabled);
        Bus::assertDispatched(SyncUserToNlpApi::class);
    }

    public function test_an_unknown_dimension_is_refused(): void
    {
        $this->asExtension();

        $this->putJson('/api/user/language/customize-witty?diversity_dimension=not-a-dimension')->assertStatus(400);
        $this->putJson('/api/user/language/customize-witty?diversity_dimension=')->assertStatus(400);
        Bus::assertNotDispatched(SyncUserToNlpApi::class);
    }
}
