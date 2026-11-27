<?php

namespace Tests\Feature;

use App\Livewire\ApiKeys\Manager;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The API keys page: a person creates, replaces and revokes their own keys,
 * for teams they are in, and sees each team's LLM limit. The NLP API keeps
 * the keys; the page shows a new key once and never keeps it.
 */
class ApiKeysPageTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'secret-key-from-the-api-0123456789abcdefgh';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config([
            'app.nlp_api_endpoint.urls' => ['https://nlp.example'],
            'app.nlp_api_endpoint.user' => 'sync',
            'app.nlp_api_endpoint.password' => 'pw',
            'app.nlp_api_endpoint.sync_rules' => true,
            'posthog.dashboard_team_id_override' => null,
        ]);
    }

    private function user(): User
    {
        $user = User::factory()->withPersonalTeam()->create(['email' => 'jane@acme.example']);
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function details(Team $team, array $overrides = []): array
    {
        return [
            'id' => 'k1', 'email' => 'jane@acme.example', 'team_id' => $team->posthogId(), 'name' => 'Editor',
            'source' => 'dashboard', 'created_at' => '2026-11-26T10:00:00Z', 'expires_at' => null, ...$overrides,
        ];
    }

    private function fakeApi(array $keys): void
    {
        Http::fake(function (Request $request) use ($keys) {
            return match (true) {
                $request->method() === 'GET' => Http::response($keys),
                str_ends_with($request->url(), '/replace') => Http::response(['key' => self::KEY, 'details' => $keys[0] ?? []], 201),
                $request->method() === 'POST' => Http::response(['key' => self::KEY, 'details' => $keys[0] ?? []], 201),
                default => Http::response(null, 204),
            };
        });
    }

    public function test_page_lists_own_keys_and_limits(): void
    {
        $user = $this->user();
        $team = $user->currentTeam;
        $team->forceFill(['llm_requests_per_minute' => 30])->save();
        $this->fakeApi([$this->details($team)]);

        $this->actingAs($user)->get('/en/user/api-keys')
            ->assertOk()
            ->assertSee('Editor')
            ->assertSee($team->name)
            ->assertSee('30')
            ->assertDontSee(self::KEY);
    }

    public function test_key_ids_are_escaped_for_javascript(): void
    {
        $user = $this->user();
        $this->fakeApi([$this->details($user->currentTeam, ['id' => "k1');alert(1);('"])]);

        $html = $this->actingAs($user)->get('/en/user/api-keys')->assertOk()->getContent();

        $this->assertStringContainsString("revoke('k1\\u0027);alert(1);(\\u0027')", $html);
        $this->assertStringContainsString("replace('k1\\u0027);alert(1);(\\u0027')", $html);
    }

    public function test_page_needs_a_verified_address(): void
    {
        $user = $this->user();
        $user->forceFill(['email_verified_at' => null])->save();
        $this->fakeApi([]);

        $this->actingAs($user)->get('/en/user/api-keys')->assertRedirect();
    }

    public function test_create_for_own_team_shows_key_once(): void
    {
        $user = $this->user();
        $team = $user->currentTeam;
        $this->fakeApi([$this->details($team)]);
        $this->actingAs($user);

        $component = Livewire::test(Manager::class)
            ->set('name', 'Editor')
            ->set('teamId', $team->id)
            ->set('expiresOn', now()->addMonth()->toDateString())
            ->call('create')
            ->assertHasNoErrors()
            ->assertDispatched('api-key-created', key: self::KEY);

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request['team_id'] === $team->posthogId() && $request['name'] === 'Editor');
        $this->assertStringNotContainsString(self::KEY, json_encode($component->snapshot));
    }

    public function test_snapshot_holds_no_key_after_next_action(): void
    {
        $user = $this->user();
        $team = $user->currentTeam;
        $this->fakeApi([$this->details($team)]);
        $this->actingAs($user);

        $component = Livewire::test(Manager::class)
            ->set('name', 'Editor')->set('teamId', $team->id)->call('create')
            ->call('$refresh');

        $this->assertStringNotContainsString(self::KEY, json_encode($component->snapshot));
        $this->assertStringNotContainsString(self::KEY, $component->html());
    }

    public function test_cannot_create_for_foreign_team(): void
    {
        $user = $this->user();
        $other = User::factory()->withPersonalTeam()->create();
        $this->fakeApi([]);
        $this->actingAs($user);

        Livewire::test(Manager::class)
            ->set('name', 'Editor')
            ->set('teamId', $other->ownedTeams()->first()->id)
            ->call('create')
            ->assertHasErrors('teamId');

        Http::assertNotSent(fn (Request $request) => $request->method() === 'POST');
    }

    public function test_expiry_must_be_in_the_future(): void
    {
        $user = $this->user();
        $this->fakeApi([]);
        $this->actingAs($user);

        Livewire::test(Manager::class)
            ->set('name', 'Editor')->set('teamId', $user->currentTeam->id)
            ->set('expiresOn', now()->subDay()->toDateString())
            ->call('create')
            ->assertHasErrors('expiresOn');
    }

    public function test_cannot_revoke_another_persons_key(): void
    {
        $user = $this->user();
        $this->fakeApi([$this->details($user->currentTeam)]);
        $this->actingAs($user);

        Livewire::test(Manager::class)->call('revoke', 'someone-elses')->assertForbidden();

        Http::assertNotSent(fn (Request $request) => $request->method() === 'DELETE');
    }

    public function test_revoke_own_key(): void
    {
        $user = $this->user();
        $this->fakeApi([$this->details($user->currentTeam)]);
        $this->actingAs($user);

        Livewire::test(Manager::class)->call('revoke', 'k1');

        Http::assertSent(fn (Request $request) => $request->method() === 'DELETE'
            && $request->url() === 'https://nlp.example/managed_keys/k1');
    }

    public function test_replace_passes_grace(): void
    {
        $user = $this->user();
        $this->fakeApi([$this->details($user->currentTeam)]);
        $this->actingAs($user);

        Livewire::test(Manager::class)
            ->set('grace.k1', 604800)
            ->call('replace', 'k1')
            ->assertDispatched('api-key-created', key: self::KEY);

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/managed_keys/k1/replace')
            && $request['grace_seconds'] === 604800);
    }

    public function test_replace_refused_for_a_team_the_user_left(): void
    {
        $user = $this->user();
        $this->fakeApi([$this->details($user->currentTeam, ['team_id' => 'dashboard-team:99999'])]);
        $this->actingAs($user);

        Livewire::test(Manager::class)->call('replace', 'k1')->assertForbidden();
    }

    public function test_page_survives_api_outage(): void
    {
        $user = $this->user();
        Http::fake(['*' => Http::response(null, 503)]);

        $this->actingAs($user)->get('/en/user/api-keys')
            ->assertOk()
            ->assertSee(__('content.api_keys_unavailable'));
    }

    public function test_menu_entry_hidden_when_api_not_configured(): void
    {
        $user = $this->user();
        $this->fakeApi([]);

        $this->actingAs($user)->get('/en/user/profile')->assertSee(__('content.api_keys_menu'));

        config(['app.nlp_api_endpoint.urls' => []]);
        $this->actingAs($user)->get('/en/user/profile')->assertDontSee(__('content.api_keys_menu'));
        $this->actingAs($user)->get('/en/user/api-keys')->assertNotFound();
    }

    public function test_actions_refused_once_the_address_is_unverified(): void
    {
        $user = $this->user();
        $this->fakeApi([$this->details($user->currentTeam)]);
        $this->actingAs($user);
        $component = Livewire::test(Manager::class);

        // Changed in another tab: the new address is not verified.
        $user->forceFill(['email' => 'boss@acme.example', 'email_verified_at' => null])->save();

        $component->set('name', 'Editor')->set('teamId', $user->currentTeam->id)->call('create')->assertForbidden();
        Livewire::test(Manager::class)->call('replace', 'k1')->assertForbidden();
        Livewire::test(Manager::class)->call('revoke', 'k1')->assertForbidden();
        Http::assertNotSent(fn (Request $request) => $request->method() !== 'GET');
    }

    public function test_page_has_an_h1_and_the_navigation_outside_main(): void
    {
        $user = $this->user();
        $this->fakeApi([]);

        $html = $this->actingAs($user)->get('/en/user/api-keys')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<main\b[^>]*id="maincontent"/', $html);
        preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $main);
        $this->assertMatchesRegularExpression('/^\s*<div[^>]*>\s*<h1\b[^>]*>\s*' . preg_quote(__('content.api_keys_title'), '/') . '\s*<\/h1>/', $main[1]);
        $this->assertStringNotContainsString('<nav', $main[1]);
        $this->assertStringContainsString('<nav', $html);
        // The sections below the h1 are h2s, not h3s.
        $this->assertStringNotContainsString('<h3', $main[1]);
    }

    public function test_the_new_key_is_announced_and_takes_focus(): void
    {
        $user = $this->user();
        $this->fakeApi([]);
        $this->actingAs($user);

        $html = Livewire::test(Manager::class)->html();

        // The live region is in the page before the key is put into it.
        $this->assertMatchesRegularExpression('/<div[^>]*role="status"[^>]*>\s*<template x-if="key">/', $html);
        $this->assertMatchesRegularExpression('/<p[^>]*x-ref="keyHeading"[^>]*tabindex="-1"/', $html);
        $this->assertMatchesRegularExpression('/x-on:api-key-created\.window="[^"]*\$nextTick\(\(\) => \$refs\.keyHeading\.focus\(\)\)/', $html);
    }

    public function test_done_returns_focus_to_the_name_field(): void
    {
        $user = $this->user();
        $this->fakeApi([]);
        $this->actingAs($user);

        $html = Livewire::test(Manager::class)->html();

        $this->assertMatchesRegularExpression('/<button[^>]*x-on:click="key = null; \$nextTick\(\(\) => document\.getElementById\(\'api-key-name\'\)\.focus\(\)\)"[^>]*>\s*' . preg_quote(__('content.api_keys_done'), '/') . '/', $html);
    }

    public function test_errors_are_linked_to_their_fields(): void
    {
        $user = $this->user();
        $this->fakeApi([]);
        $this->actingAs($user);

        $html = Livewire::test(Manager::class)
            ->set('name', '')->set('teamId', null)->set('expiresOn', now()->subDay()->toDateString())
            ->call('create')
            ->assertHasErrors(['name', 'teamId', 'expiresOn'])
            ->html();

        foreach (['api-key-name' => 'input', 'api-key-team' => 'select', 'api-key-expires' => 'input'] as $id => $tag) {
            $this->assertMatchesRegularExpression('/<' . $tag . ' id="' . $id . '"[^>]*aria-invalid="true"[^>]*aria-describedby="' . $id . '-error"/', $html, $id);
            $this->assertMatchesRegularExpression('/<p id="' . $id . '-error"[^>]*role="alert"/', $html, $id);
        }
    }

    public function test_fields_without_errors_are_not_marked(): void
    {
        $user = $this->user();
        $this->fakeApi([]);
        $this->actingAs($user);

        $html = Livewire::test(Manager::class)->html();

        $this->assertStringNotContainsString('aria-invalid', $html);
    }
}
