<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * /write: one page for checking and rewriting text with Witty's editor,
 * replacing Witty GPT, signed in with the dashboard's own OAuth tokens.
 */
class WritePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The page's script is built by Vite; the tests look at the HTML.
        $this->withoutVite();
        config([
            'app.url' => 'https://dashboard.example.com',
            'app.nlp_api_public_url' => 'https://api.example.com/',
            'passport.editor.client_id' => 'editor-client',
        ]);
    }

    private function signedInUser(?User $user = null): User
    {
        $user ??= User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    /** The JSON the page reads its settings from. */
    private function pageConfig(string $html): array
    {
        $this->assertMatchesRegularExpression('#<script type="application/json" id="write-config">(.*?)</script>#s', $html);
        preg_match('#<script type="application/json" id="write-config">(.*?)</script>#s', $html, $match);

        return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->withoutLocaleRedirects()->get('/write')->assertRedirectContains('/login');
    }

    public function test_unverified_users_are_sent_to_verify_their_email(): void
    {
        $user = $this->signedInUser(User::factory()->unverified()->withPersonalTeam()->create());

        $this->actingAs($user)->withoutLocaleRedirects()->get('/write')
            ->assertRedirectContains('/email/verify');
    }

    public function test_the_page_carries_its_config(): void
    {
        $response = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/write')
            ->assertOk()->assertViewIs('write');

        $config = $this->pageConfig($response->getContent());
        $this->assertSame('editor-client', $config['clientId']);
        $this->assertSame('https://api.example.com/', $config['apiUrl']);
        $this->assertSame('https://dashboard.example.com/write/callback', $config['redirectUri']);
        $this->assertStringEndsWith('/oauth/authorize', $config['authorizeUrl']);
        $this->assertStringEndsWith('/oauth/token', $config['tokenUrl']);
        // Where the script returns after the callback, without its code.
        $this->assertStringEndsWith('/write', $config['pageUrl']);
    }

    public function test_the_page_passes_its_messages(): void
    {
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/write')
            ->assertOk()->getContent();

        preg_match('#<script type="application/json" id="write-messages">(.*?)</script>#s', $html, $match);
        $messages = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(
            ['noAccess', 'reload', 'editorLabel', 'signInFailed', 'rewriting', 'rewriteFailed', 'llmRefused'],
            array_keys($messages),
        );
        $this->assertNotContains('', $messages);
    }

    public function test_the_callback_has_no_locale_prefix(): void
    {
        $this->actingAs($this->signedInUser())->get('/write/callback')
            ->assertOk()->assertViewIs('write');
    }

    public function test_prompt_redirects_to_write(): void
    {
        $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/prompt')
            ->assertRedirectContains('/write');
    }

    public function test_page_without_api_url_says_so(): void
    {
        config(['app.nlp_api_public_url' => '']);

        $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/write')
            ->assertOk()
            ->assertSee(__('content.write_not_configured'))
            ->assertDontSee('id="write-editor"', false);
    }

    public function test_llm_off_is_passed_to_the_page(): void
    {
        config(['app.llm_enabled' => false]);

        $response = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/write')
            ->assertOk();
        $this->assertFalse($this->pageConfig($response->getContent())['llmEnabled']);
    }

    public function test_the_prompt_is_offered_only_where_the_team_has_the_llm_on(): void
    {
        // The NLP API refuses /v1.0/write for a team whose synced setting
        // forces LLM alternatives off; a prompt there would only be refused.
        config(['app.llm_enabled' => true]);
        $user = $this->signedInUser();
        $team = $user->currentTeam;
        $user->forceFill(['license_team_id' => $team->id])->save();

        $page = fn () => $this->pageConfig(
            $this->actingAs($user->refresh())->withoutLocaleRedirects()->get('/write')->assertOk()->getContent()
        )['llmEnabled'];

        $team->forceFill(['llm_alternatives' => null])->save();
        $this->assertFalse($page());

        $team->forceFill(['llm_alternatives' => false])->save();
        $this->assertFalse($page());

        $team->forceFill(['llm_alternatives' => true])->save();
        $this->assertTrue($page());
    }

    public function test_the_prompt_follows_the_licence_team_not_the_current_one(): void
    {
        // The NLP API applies the licence team's settings (SyncUserToNlpApi
        // sends it as the person's organisation), whichever team is current.
        config(['app.llm_enabled' => true]);
        $user = $this->signedInUser();
        $licence = $user->currentTeam;
        $other = User::factory()->withPersonalTeam()->create()->ownedTeams()->firstOrFail();
        $other->users()->attach($user, ['role' => 'user']);
        $user->forceFill(['license_team_id' => $licence->id])->save();
        $user->switchTeam($other);

        $page = fn () => $this->pageConfig(
            $this->actingAs($user->refresh())->withoutLocaleRedirects()->get('/write')->assertOk()->getContent()
        )['llmEnabled'];

        $licence->forceFill(['llm_alternatives' => true])->save();
        $other->forceFill(['llm_alternatives' => false])->save();
        $this->assertTrue($page());

        $licence->forceFill(['llm_alternatives' => false])->save();
        $other->forceFill(['llm_alternatives' => true])->save();
        $this->assertFalse($page());
    }

    public function test_the_config_cannot_close_its_script_element(): void
    {
        config(['app.support_email' => '</script><script>alert(1)</script>']);

        $response = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/write');
        $this->assertStringNotContainsString('</script><script>alert(1)', $response->getContent());
    }
}
