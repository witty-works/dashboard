<?php

namespace Tests\Feature\Accessibility;

use App\Livewire\ApiKeys\Manager;
use App\Livewire\Teams\ApiKeys as TeamApiKeys;
use App\Livewire\Teams\TeamMemberManagerHelpHero;
use App\Models\Domain;
use App\Models\FalsePositive;
use App\Models\TermReplacement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The edit buttons of the guideline lists lead to the field they fill
 * (resources/js/app.js moves focus there, WCAG 2.4.3), and buttons that
 * repeat on every row say which row they act on (WCAG 2.4.6).
 */
class FocusAndNamesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create(['name' => 'Jane Owner', 'email' => 'jane@acme.example']);
        $team = $user->ownedTeams()->firstOrFail();
        $user->forceFill(['license_team_id' => $team->id])->save();
        $user->switchTeam($team);

        return $user->refresh();
    }

    public static function editLists(): array
    {
        return [
            'personal dictionary' => ['/user/language/dictionary', TermReplacement::class, 'user'],
            'team dictionary' => ['/team/language/dictionary', TermReplacement::class, 'team'],
            'personal ignored words' => ['/user/language/ignore-words', FalsePositive::class, 'user'],
            'team ignored words' => ['/team/language/ignored-words', FalsePositive::class, 'team'],
            'personal privacy settings' => ['/user/language/privacy-settings', Domain::class, 'user'],
            'team privacy settings' => ['/team/language/privacy-settings', Domain::class, 'team'],
        ];
    }

    #[DataProvider('editLists')]
    public function test_edit_buttons_lead_to_a_field_on_the_page(string $url, string $class, string $owner): void
    {
        $user = $this->signedInUser();
        $values = match ($class) {
            TermReplacement::class => ['term' => 'chairman', 'replacement' => 'chair', 'language_code' => 'en'],
            FalsePositive::class => ['false_positive' => 'blacklist'],
            Domain::class => ['domain' => 'acme.example'],
        };
        $class::forceCreate([...$values, ($owner === 'user' ? 'user_id' : 'team_id') => $owner === 'user' ? $user->id : $user->currentTeam->id]);

        $html = $this->actingAs($user)->withoutLocaleRedirects()->get($url)->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/data-scroll-to="([^"]+)"/', $html, $targets));
        $this->assertMatchesRegularExpression('/<(input|textarea|select)\b[^>]*\bid="' . preg_quote($targets[1][0], '/') . '"/', $html);
    }

    public function test_team_member_buttons_name_the_member(): void
    {
        $owner = $this->signedInUser();
        $team = $owner->currentTeam;
        $member = User::factory()->withPersonalTeam()->create(['name' => 'Sam Admin']);
        $team->users()->attach($member, ['role' => 'admin']);
        $this->actingAs($owner);

        $html = Livewire::test(TeamMemberManagerHelpHero::class, ['team' => $team->fresh()])->html();

        $this->assertMatchesRegularExpression('/' . preg_quote(__('content.remove'), '/') . '\s+<span class="sr-only">' . preg_quote(e('Sam Admin'), '/') . '<\/span>/', $html);
        // The visible "make owner" is hidden from screen readers, which hear the sentence instead.
        $this->assertMatchesRegularExpression('/<span aria-hidden="true">' . preg_quote(__('content.make_owner'), '/') . '<\/span><span class="sr-only">' . preg_quote(e(__('content.make_owner_of', ['name' => 'Sam Admin'])), '/') . '<\/span>/', $html);
        $this->assertMatchesRegularExpression('/' . preg_quote(__('content.admin'), '/') . '\s*<span class="sr-only">' . preg_quote(e(__('content.change_role_of', ['name' => 'Sam Admin'])), '/') . '<\/span>/', $html);
    }

    private function fakeKeys(array $keys): void
    {
        config([
            'app.nlp_api_endpoint.urls' => ['https://nlp.example'],
            'app.nlp_api_endpoint.user' => 'sync',
            'app.nlp_api_endpoint.password' => 'pw',
            'app.nlp_api_endpoint.sync_rules' => true,
            'posthog.dashboard_team_id_override' => null,
        ]);
        Http::fake(fn () => Http::response($keys));
    }

    public function test_api_key_buttons_name_the_key(): void
    {
        $user = $this->signedInUser();
        $this->fakeKeys([[
            'id' => 'k1', 'email' => 'jane@acme.example', 'team_id' => $user->currentTeam->posthogId(), 'name' => 'Editor',
            'source' => 'dashboard', 'created_at' => '2026-11-26T10:00:00Z', 'expires_at' => null,
        ]]);
        $this->actingAs($user);

        $html = Livewire::test(Manager::class)->html();
        $suffix = preg_quote(e('Editor'), '/');

        $this->assertMatchesRegularExpression('/' . preg_quote(__('content.api_keys_revoke'), '/') . ' <span class="sr-only">' . $suffix . '<\/span>/', $html);
        $this->assertMatchesRegularExpression('/' . preg_quote(__('content.api_keys_replace'), '/') . ' <span class="sr-only">' . $suffix . '<\/span>/', $html);
        $this->assertMatchesRegularExpression('/<label class="sr-only" for="grace-k1">' . preg_quote(e(__('content.api_keys_grace_for', ['name' => 'Editor'])), '/') . '<\/label>/', $html);
    }

    public function test_team_api_key_buttons_name_the_key_and_its_owner(): void
    {
        $user = $this->signedInUser();
        $this->fakeKeys([[
            'id' => 'k1', 'email' => 'sam@acme.example', 'team_id' => $user->currentTeam->posthogId(), 'name' => 'Editor',
            'source' => 'dashboard', 'created_at' => '2026-11-26T10:00:00Z', 'expires_at' => null,
        ]]);
        $this->actingAs($user);

        $html = Livewire::test(TeamApiKeys::class, ['team' => $user->currentTeam])->html();

        $this->assertMatchesRegularExpression('/' . preg_quote(__('content.api_keys_revoke'), '/') . ' <span class="sr-only">' . preg_quote(e('Editor (sam@acme.example)'), '/') . '<\/span>/', $html);
    }
}
