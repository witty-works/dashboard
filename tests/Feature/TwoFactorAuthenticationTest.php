<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\FakesOfficeTokens;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * Two-factor sign-in (TOTP, Fortify's) is open to every user. It is set up on
 * the profile page and only takes effect once a code from the app has been
 * entered; from then on, signing in with a password asks for a code or a
 * recovery code.
 */
class TwoFactorAuthenticationTest extends TestCase
{
    use FakesOfficeTokens;
    use RefreshDatabase;
    use UsesTwoFactor;

    private const CLIENT_ID = '00000000-0000-0000-0000-00000000c1d0';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    private function user(): User
    {
        $user = User::factory()->withPersonalTeam()->create([
            'password' => Hash::make('secret-password'),
            'has_consented_to_terms_of_service' => true,
        ]);
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    private function signIn(User $user)
    {
        return $this->post(route('login'), ['email' => $user->email, 'password' => 'secret-password']);
    }

    public function test_the_profile_page_offers_two_factor_sign_in(): void
    {
        $this->actingAs($this->user())
            ->get('/en/user/profile')
            ->assertOk()
            ->assertSeeLivewire('profile.two-factor-authentication-form')
            ->assertSee(__('content.two_factor_authentication'));
    }

    public function test_a_user_enables_and_confirms_two_factor(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        session(['auth.password_confirmed_at' => time()]);

        $form = Livewire::test(TwoFactorAuthenticationForm::class)
            ->call('enableTwoFactorAuthentication')
            ->assertSet('showingQrCode', true)
            ->assertSet('showingConfirmation', true);

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertFalse($user->hasEnabledTwoFactorAuthentication(), 'Active before a code was confirmed.');
        $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        $form->assertSee($secret);

        $form->set('code', $this->otp($secret))
            ->call('confirmTwoFactorAuthentication')
            ->assertHasNoErrors()
            ->assertSet('showingRecoveryCodes', true)
            ->assertSee($user->recoveryCodes()[0]);

        $this->assertTrue($user->refresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_enabling_requires_a_recently_confirmed_password(): void
    {
        $this->actingAs($this->user());

        Livewire::test(TwoFactorAuthenticationForm::class)
            ->call('enableTwoFactorAuthentication')
            ->assertForbidden();
    }

    public function test_a_wrong_confirmation_code_leaves_two_factor_off(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        session(['auth.password_confirmed_at' => time()]);

        Livewire::test(TwoFactorAuthenticationForm::class)
            ->call('enableTwoFactorAuthentication')
            ->set('code', '000000')
            ->call('confirmTwoFactorAuthentication')
            ->assertHasErrors('code');

        $this->assertFalse($user->refresh()->hasEnabledTwoFactorAuthentication());
    }

    public function test_an_unconfirmed_setup_does_not_change_sign_in(): void
    {
        $user = $this->user();
        $this->actingAs($user);
        session(['auth.password_confirmed_at' => time()]);
        Livewire::test(TwoFactorAuthenticationForm::class)->call('enableTwoFactorAuthentication');
        $this->post(route('logout'));

        $this->signIn($user);

        $this->assertAuthenticatedAs($user);
    }

    public function test_sign_in_asks_for_the_code_once_two_factor_is_confirmed(): void
    {
        $user = $this->user();
        $secret = $this->confirmTwoFactor($user);

        $this->signIn($user)->assertRedirect(route('two-factor.login'));
        $this->assertGuest();

        $this->get(route('two-factor.login'))
            ->assertOk()
            ->assertSee(__('content.two_factor_challenge_code'));

        $this->post(route('two-factor.login.store'), ['code' => $this->otp($secret)])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_code_is_refused(): void
    {
        $user = $this->user();
        $this->confirmTwoFactor($user);

        $this->signIn($user);
        $this->post(route('two-factor.login.store'), ['code' => '000000'])
            ->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
    }

    public function test_a_recovery_code_signs_in_once(): void
    {
        $user = $this->user();
        $this->confirmTwoFactor($user, ['recovery-code-one', 'recovery-code-two']);

        $this->signIn($user);
        $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-one'])->assertRedirect();
        $this->assertAuthenticatedAs($user);

        $codes = $user->refresh()->recoveryCodes();
        $this->assertNotContains('recovery-code-one', $codes);
        $this->assertContains('recovery-code-two', $codes);

        $this->post(route('logout'));
        $this->signIn($user);
        $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-one']);
        $this->assertGuest();
    }

    public function test_the_code_challenge_is_rate_limited(): void
    {
        $user = $this->user();
        $this->confirmTwoFactor($user);
        $this->signIn($user);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('two-factor.login.store'), ['code' => '000000']);
        }

        $this->post(route('two-factor.login.store'), ['code' => '000000'])->assertStatus(429);
    }

    public function test_office_sign_in_does_not_ask_for_the_code(): void
    {
        // Microsoft's sign-in handles the second factor for Office accounts;
        // superadmins still need a code for /admin (SuperadminTwoFactorTest).
        config(['services.microsoft_office.client_id' => self::CLIENT_ID]);
        $user = $this->user();
        $this->confirmTwoFactor($user);

        $this->post('/en/office-login', ['token' => $this->token(['email' => $user->email, 'xms_edov' => true]), 'confirm' => 1]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_the_password_page_of_fortifys_two_factor_routes_renders(): void
    {
        // Fortify's own endpoints (POST /user/two-factor-authentication and
        // the others) send to it when the password was not entered recently.
        $this->actingAs($this->user())
            ->get(route('password.confirm'))
            ->assertOk()
            ->assertSee(__('content.for_your_security_confirm'));
    }

    public function test_a_client_sign_in_asks_for_the_code_then_returns_to_the_client(): void
    {
        // The browser extension and the write page sign in through
        // /oauth/authorize, which sends guests to the same sign-in.
        $client = new OAuthClient();
        $client->forceFill([
            'name' => 'Witty Browser Extension',
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => ['https://example.test/callback'],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();
        config(['passport.first_party_clients' => [(string) $client->getKey()]]);
        $user = $this->user();
        $secret = $this->confirmTwoFactor($user);

        $authorize = '/oauth/authorize?' . http_build_query([
            'client_id' => $client->getKey(),
            'redirect_uri' => 'https://example.test/callback',
            'response_type' => 'code',
            'state' => str_repeat('s', 40),
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', str_repeat('v', 64), true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
        $this->get($authorize)->assertRedirect();

        $this->signIn($user)->assertRedirect(route('two-factor.login'));
        $this->assertGuest();

        $back = $this->post(route('two-factor.login.store'), ['code' => $this->otp($secret)]);
        $back->assertRedirect();
        $this->assertStringStartsWith(url('/oauth/authorize?'), $back->headers->get('Location'));

        $this->assertStringStartsWith('https://example.test/callback?code=', $this->get($authorize)->headers->get('Location'));
    }

    #[DataProvider('otherLocales')]
    public function test_the_errors_from_fortify_and_jetstream_are_translated(string $locale): void
    {
        App::setLocale($locale);

        foreach ([
            'The provided two factor authentication code was invalid.',
            'The provided two factor recovery code was invalid.',
            'This password does not match our records.',
        ] as $message) {
            $this->assertNotSame($message, __($message), "[$locale] $message");
        }
    }

    public static function otherLocales(): array
    {
        return ['german' => ['de'], 'french' => ['fr']];
    }
}
