<?php

namespace Tests\Feature;

use App\Auth\ExtensionUserResolver;
use App\Helpers\OfficeSsoHelper;
use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\FakesOfficeTokens;
use Tests\TestCase;

/**
 * Which dashboard user an Office token stands for. Tokens come from any Entra
 * tenant, and Microsoft documents `preferred_username` and `email` as values
 * a tenant's admin can set. So a user is found by the Microsoft account
 * already linked to them (`sub`, immutable), or else by `email` only when
 * `xms_edov: true` says the tenant verified that address's domain.
 */
class OfficeSsoIdentityTest extends TestCase
{
    use FakesOfficeTokens;

    use RefreshDatabase;

    private const CLIENT_ID = '00000000-0000-0000-0000-00000000c1d0';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config(['services.microsoft_office.client_id' => self::CLIENT_ID]);
        config(['services.microsoft_office.redirect_uri' => 'https://office.example/done']);
    }

    private function victim(): User
    {
        $user = User::factory()->withPersonalTeam()->create([
            'email' => 'victim@example.com',
            'has_consented_to_terms_of_service' => true,
        ]);

        return $user->refresh();
    }

    private function resolve(string $token): ?User
    {
        $request = Request::create('/api/user/language', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $token);

        return $this->app->make(ExtensionUserResolver::class)($request);
    }

    private function link(User $user, string $sub): void
    {
        ConnectedAccount::forceCreate([
            'user_id' => $user->id,
            'provider' => 'microsoft_office',
            'provider_id' => $sub,
            'email' => $user->email,
        ]);
    }

    // --- The add-in's API calls ---

    public function test_the_add_in_is_not_let_in_on_preferred_username(): void
    {
        $this->victim();

        $this->assertNull($this->resolve($this->token(['preferred_username' => 'victim@example.com'])));
    }

    public function test_the_add_in_is_not_let_in_on_an_unverified_email(): void
    {
        $this->victim();

        $this->assertNull($this->resolve($this->token(['email' => 'victim@example.com', 'xms_edov' => false])));
        $this->assertNull($this->resolve($this->token(['email' => 'victim@example.com'])));
    }

    public function test_the_add_in_is_let_in_on_a_verified_email(): void
    {
        $victim = $this->victim();

        $user = $this->resolve($this->token(['email' => 'Victim@Example.com', 'xms_edov' => true]));

        $this->assertTrue($victim->is($user));
    }

    public function test_the_add_in_is_let_in_on_a_linked_account(): void
    {
        $victim = $this->victim();
        $this->link($victim, 'sub-victim');

        $user = $this->resolve($this->token(['sub' => 'sub-victim']));

        $this->assertTrue($victim->is($user));
    }

    // --- Signing in on the dashboard ---

    public function test_sign_in_is_refused_on_preferred_username(): void
    {
        $this->victim();

        $this->post('/en/office-login', ['token' => $this->token(['preferred_username' => 'victim@example.com']), 'confirm' => 1]);

        $this->assertGuest();
    }

    public function test_sign_in_works_on_a_verified_email(): void
    {
        $victim = $this->victim();

        $this->post('/en/office-login', ['token' => $this->token(['email' => 'victim@example.com', 'xms_edov' => true]), 'confirm' => 1]);

        $this->assertAuthenticatedAs($victim);
    }

    public function test_sign_in_works_on_a_linked_account(): void
    {
        $victim = $this->victim();
        $this->link($victim, 'sub-victim');

        $this->post('/en/office-login', ['token' => $this->token(['sub' => 'sub-victim']), 'confirm' => 1]);

        $this->assertAuthenticatedAs($victim);
    }

    public function test_a_linked_account_wins_over_a_different_verified_email(): void
    {
        $victim = $this->victim();
        $linked = User::factory()->withPersonalTeam()->create(['email' => 'linked@example.com', 'has_consented_to_terms_of_service' => true]);
        $this->link($linked, 'sub-linked');

        $user = $this->resolve($this->token(['sub' => 'sub-linked', 'email' => 'victim@example.com', 'xms_edov' => true]));

        $this->assertTrue($linked->is($user));
        $this->assertFalse($victim->is($user));
    }

    public function test_signing_in_on_a_verified_email_links_the_microsoft_account(): void
    {
        $victim = $this->victim();

        $this->post('/en/office-login', ['token' => $this->token(['email' => 'victim@example.com', 'xms_edov' => true, 'sub' => 'sub-victim']), 'confirm' => 1]);

        $this->assertDatabaseHas('connected_accounts', ['user_id' => $victim->id, 'provider' => 'microsoft_office', 'provider_id' => 'sub-victim']);
    }

    public function test_a_link_to_a_deleted_user_does_not_break_sign_up(): void
    {
        ConnectedAccount::forceCreate(['user_id' => 999999, 'provider' => 'microsoft_office', 'provider_id' => 'sub-orphan']);

        $this->post('/en/office-register', [
            'token' => $this->token(['email' => 'someone@example.com', 'xms_edov' => true, 'sub' => 'sub-orphan']),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ])->assertRedirect('https://office.example/done?status=success');

        $user = User::where('email', 'someone@example.com')->firstOrFail();
        $this->assertDatabaseHas('connected_accounts', ['user_id' => $user->id, 'provider_id' => 'sub-orphan']);
        $this->assertDatabaseMissing('connected_accounts', ['user_id' => 999999]);
    }

    // --- Signing up ---

    public function test_sign_up_does_not_link_an_existing_account_on_preferred_username(): void
    {
        $victim = $this->victim();

        $this->post('/en/office-register', [
            'token' => $this->token(['preferred_username' => 'victim@example.com']),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('connected_accounts', ['user_id' => $victim->id]);
    }

    public function test_sign_up_does_not_create_an_account_for_an_unverified_email(): void
    {
        $this->post('/en/office-register', [
            'token' => $this->token(['preferred_username' => 'someone@example.com', 'email' => 'someone@example.com']),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'someone@example.com']);
    }

    public function test_sign_up_with_a_verified_email_creates_a_linked_account(): void
    {
        $this->post('/en/office-register', [
            'token' => $this->token(['email' => 'someone@example.com', 'xms_edov' => true, 'sub' => 'sub-someone']),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ]);

        $user = User::where('email', 'someone@example.com')->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertDatabaseHas('connected_accounts', ['user_id' => $user->id, 'provider_id' => 'sub-someone']);
    }

    // --- An address the dashboard has not verified ---
    //
    // Anyone signed in can change their address on the profile page to a
    // colleague's; it stays unverified, and they keep signing in with their
    // password. Microsoft verifying that address for the colleague must not
    // hand the colleague's Microsoft account to them.

    private function holderOfAnUnverifiedAddress(): User
    {
        $user = User::factory()->withPersonalTeam()->unverified()->create([
            'email' => 'victim@example.com',
            'has_consented_to_terms_of_service' => true,
        ]);

        return $user->refresh();
    }

    private function verifiedToken(): string
    {
        return $this->token(['email' => 'victim@example.com', 'xms_edov' => true, 'sub' => 'sub-victim']);
    }

    public function test_the_add_in_is_not_let_in_on_an_address_the_dashboard_has_not_verified(): void
    {
        $this->holderOfAnUnverifiedAddress();

        $this->assertNull($this->resolve($this->verifiedToken()));
    }

    public function test_sign_in_is_refused_on_an_address_the_dashboard_has_not_verified(): void
    {
        $holder = $this->holderOfAnUnverifiedAddress();

        $this->post('/en/office-login', ['token' => $this->verifiedToken(), 'confirm' => 1])
            ->assertRedirect('https://office.example/done?status=unverified_email');

        $this->assertGuest();
        $this->assertDatabaseMissing('connected_accounts', ['user_id' => $holder->id]);
    }

    public function test_sign_up_is_refused_on_an_address_the_dashboard_has_not_verified(): void
    {
        $holder = $this->holderOfAnUnverifiedAddress();

        $this->post('/en/office-register', [
            'token' => $this->verifiedToken(),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ])->assertRedirect('https://office.example/done?status=unverified_email');

        $this->assertGuest();
        $this->assertDatabaseMissing('connected_accounts', ['user_id' => $holder->id]);
        $this->assertSame(1, User::where('email', 'victim@example.com')->count());
    }

    public function test_the_add_in_page_explains_an_unverified_address(): void
    {
        $this->get('/word-addin?status=unverified_email')
            ->assertSee(__('content.witty_word_addin_text_unverified_email', ['support_email' => e(config('app.support_email'))]), false);
    }

    // --- Restricting the tenant ---

    public function test_a_token_from_another_tenant_is_refused_when_the_tenant_is_set(): void
    {
        config(['services.microsoft_office.tenant_id' => 'a3b1c2d4-5e6f-4a7b-9c8d-0e1f2a3b4c5d']);
        $encode = fn (array $part) => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');
        $token = $encode(['alg' => 'RS256']) . '.' . $encode([
            'aud' => self::CLIENT_ID,
            'tid' => '6f1c2b9a-3d4e-4f5a-8b6c-7d8e9f0a1b2c',
            'iss' => 'https://login.microsoftonline.com/6f1c2b9a-3d4e-4f5a-8b6c-7d8e9f0a1b2c/v2.0',
            'exp' => time() + 600,
        ]) . '.sig';

        $this->expectException(\Laravel\Socialite\Two\InvalidStateException::class);
        $this->expectExceptionMessage('tid is not the configured tenant');
        (new OfficeSsoHelper())->validateIdToken($token);
    }

    public function test_a_token_for_another_application_is_refused_before_anything_is_fetched(): void
    {
        $encode = fn (array $part) => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');
        $token = $encode(['alg' => 'RS256']) . '.' . $encode([
            'aud' => 'prefix-' . self::CLIENT_ID,
            'tid' => '6f1c2b9a-3d4e-4f5a-8b6c-7d8e9f0a1b2c',
            'exp' => time() + 600,
        ]) . '.sig';

        $this->expectException(\Laravel\Socialite\Two\InvalidStateException::class);
        $this->expectExceptionMessage('aud on id_token does not match');
        (new OfficeSsoHelper())->validateIdToken($token);
    }

    // --- Keeping the token out of addresses and logs ---

    public function test_the_sign_in_page_takes_the_token_from_the_fragment_not_the_query(): void
    {
        $victim = $this->victim();

        $this->get('/en/office-login?token=' . $this->token(['email' => 'victim@example.com', 'xms_edov' => true], validated: false))
            ->assertOk()
            ->assertSee('location.hash', false)
            ->assertDontSee('victim@example.com');

        $this->assertGuest();
    }

    public function test_the_sign_up_page_takes_the_token_from_the_fragment_not_the_query(): void
    {
        $this->get('/en/office-register?token=' . $this->token(['email' => 'someone@example.com', 'xms_edov' => true], validated: false))
            ->assertOk()
            ->assertSee('location.hash', false)
            ->assertDontSee('someone@example.com');
    }

    public function test_signing_in_asks_who_to_continue_as_first(): void
    {
        $this->victim();
        $token = $this->token(['email' => 'victim@example.com', 'xms_edov' => true]);

        $this->post('/en/office-login', ['token' => $token])
            ->assertOk()
            ->assertSee('victim@example.com')
            ->assertSee('name="confirm"', false);

        $this->assertGuest();
    }

    public function test_someone_already_signed_in_as_that_user_is_not_asked(): void
    {
        $victim = $this->victim();
        $this->actingAs($victim);

        $this->post('/en/office-login', ['token' => $this->token(['email' => 'victim@example.com', 'xms_edov' => true])])
            ->assertRedirect();

        $this->assertAuthenticatedAs($victim);
    }

    public function test_a_failed_token_is_not_emailed(): void
    {
        config(['mail.default' => 'array']);
        putenv('OFFICE_SSO_DEBUG_EMAIL=debug@example.com');
        $_ENV['OFFICE_SSO_DEBUG_EMAIL'] = $_SERVER['OFFICE_SSO_DEBUG_EMAIL'] = 'debug@example.com';
        $helper = \Mockery::mock(OfficeSsoHelper::class)->makePartial();
        $helper->shouldReceive('validateIdToken')->andThrow(new \Laravel\Socialite\Two\InvalidStateException('bad'));
        $this->app->instance(OfficeSsoHelper::class, $helper);

        try {
            $this->post('/en/office-register', ['token' => 'secret-token'])
                ->assertRedirect('https://office.example/done?status=failed');
        } finally {
            putenv('OFFICE_SSO_DEBUG_EMAIL');
            unset($_ENV['OFFICE_SSO_DEBUG_EMAIL'], $_SERVER['OFFICE_SSO_DEBUG_EMAIL']);
        }

        $this->assertCount(0, Mail::mailer('array')->getSymfonyTransport()->messages());
    }

    public function test_sign_up_without_consent_shows_the_form_again_with_the_token(): void
    {
        $token = $this->token(['email' => 'someone@example.com', 'xms_edov' => true]);

        $this->post('/en/office-register', [
            'token' => $token,
            'has_consented_to_terms_of_service' => 0,
            'has_consented_to_mailing' => 0,
        ])->assertStatus(422)
            ->assertSee('consent-error', false)
            ->assertSee($token, false);

        $this->assertDatabaseMissing('users', ['email' => 'someone@example.com']);
    }

    public function test_the_pages_that_handle_the_token_load_no_tag_manager(): void
    {
        $this->victim();

        $this->get('/en/office-login')->assertOk()->assertDontSee('googletagmanager');
        $this->post('/en/office-login', ['token' => $this->token(['email' => 'victim@example.com', 'xms_edov' => true])])
            ->assertOk()
            ->assertDontSee('googletagmanager');
    }

    public function test_someone_signed_in_as_another_user_is_told_before_switching(): void
    {
        $this->victim();
        $other = User::factory()->withPersonalTeam()->create(['email' => 'other@example.com']);
        $this->actingAs($other);

        $this->post('/en/office-login', ['token' => $this->token(['email' => 'victim@example.com', 'xms_edov' => true])])
            ->assertOk()
            ->assertSee('other@example.com')
            ->assertSee('victim@example.com');

        $this->assertAuthenticatedAs($other);
    }

    public function test_a_malformed_token_is_refused_not_a_server_error(): void
    {
        $this->post('/en/office-login', ['token' => ['a']])->assertRedirect('https://office.example/done?status=failed');
        $this->post('/en/office-register', ['token' => ['a']])->assertRedirect('https://office.example/done?status=failed');
    }

    public function test_a_token_whose_tenant_is_not_a_string_is_refused(): void
    {
        config(['services.microsoft_office.tenant_id' => 'a3b1c2d4-5e6f-4a7b-9c8d-0e1f2a3b4c5d']);
        $encode = fn (array $part) => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');
        $token = $encode(['alg' => 'RS256']) . '.' . $encode(['aud' => self::CLIENT_ID, 'tid' => ['x'], 'exp' => time() + 600]) . '.sig';

        $this->expectException(\Laravel\Socialite\Two\InvalidStateException::class);
        (new OfficeSsoHelper())->validateIdToken($token);
    }
}
