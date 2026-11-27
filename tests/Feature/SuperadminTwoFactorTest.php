<?php

namespace Tests\Feature;

use App\Livewire\Admin\Teams;
use App\Livewire\Admin\Users;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\Http\Livewire\TwoFactorAuthenticationForm;
use Livewire\Livewire;
use Tests\Concerns\FakesOfficeTokens;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * Superadmins must use two-factor sign-in. Without it confirmed they are sent
 * to the profile page to set it up before /admin or impersonation; with it,
 * the session must have seen a code (at sign-in, when setting it up, or on
 * the check page), whichever way they signed in.
 */
class SuperadminTwoFactorTest extends TestCase
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

    private function superadmin(): User
    {
        return tap($this->user())->assignRole(config('admin.role'));
    }

    private function admin()
    {
        return $this->withoutLocaleRedirects()->get('/admin/users');
    }

    // --- Without two-factor sign-in ---

    public function test_a_superadmin_without_two_factor_is_sent_to_set_it_up(): void
    {
        $this->actingAs($this->superadmin());

        $this->admin()
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('flash.banner', __('content.superadmin_two_factor_required'));

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee(__('content.superadmin_two_factor_required'));
    }

    public function test_a_superadmin_without_two_factor_cannot_impersonate(): void
    {
        $admin = $this->superadmin();
        $user = $this->user();
        $this->assertFalse($admin->canImpersonate());

        $this->actingAs($admin)->get('/en/impersonate/take/' . $user->id)
            ->assertRedirect(route('profile.show'));

        $this->assertSame($admin->id, auth()->id());
    }

    public function test_the_admin_components_refuse_a_superadmin_without_two_factor(): void
    {
        $admin = $this->superadmin();

        Livewire::actingAs($admin)->test(Users::class)->assertForbidden();
        Livewire::actingAs($admin)->test(Teams::class)->assertForbidden();
    }

    public function test_an_unconfirmed_setup_does_not_count(): void
    {
        $admin = $this->superadmin();
        $this->confirmTwoFactor($admin);
        $admin->forceFill(['two_factor_confirmed_at' => null])->save();
        session()->put(\App\Support\SuperadminTwoFactor::SESSION_KEY, $admin->id);

        $this->actingAs($admin->refresh());

        $this->admin()->assertRedirect(route('profile.show'));
        $this->assertFalse($admin->canImpersonate());
    }

    public function test_ordinary_users_are_not_asked_for_two_factor(): void
    {
        $this->actingAs($this->user())->get('/en/user/profile')
            ->assertOk()
            ->assertDontSee(__('content.superadmin_two_factor_required'));
    }

    public function test_a_superadmin_without_two_factor_still_uses_the_other_pages(): void
    {
        $this->actingAs($this->superadmin())->get('/en/user/profile')->assertOk();
    }

    public function test_a_superadmin_without_a_team_is_led_to_the_setup_in_invite_only_mode(): void
    {
        config(['auth.invite_only' => true]);
        $admin = tap(User::factory()->create())->assignRole(config('admin.role'));

        $this->actingAs($admin)->followingRedirects()->get(route('root'))
            ->assertOk()
            ->assertSee(__('content.superadmin_two_factor_required'));
    }

    // --- With two-factor sign-in ---

    public function test_a_verified_superadmin_reaches_admin_and_impersonates(): void
    {
        $admin = $this->verifiedSuperadmin($this->user());
        $user = $this->user();

        $this->actingAs($admin);
        $this->admin()->assertOk();
        Livewire::actingAs($admin)->test(Users::class)->assertOk();

        $this->get('/en/impersonate/take/' . $user->id)->assertRedirect();
        $this->assertSame($user->id, auth()->id());
    }

    public function test_the_code_at_sign_in_opens_admin(): void
    {
        $admin = $this->superadmin();
        $secret = $this->confirmTwoFactor($admin);

        $this->post(route('login'), ['email' => $admin->email, 'password' => 'secret-password'])
            ->assertRedirect(route('two-factor.login'));
        $this->post(route('two-factor.login.store'), ['code' => $this->otp($secret)]);

        $this->assertAuthenticatedAs($admin);
        $this->admin()->assertOk();
    }

    public function test_setting_up_two_factor_opens_admin_in_the_same_session(): void
    {
        $admin = $this->superadmin();
        $this->actingAs($admin);
        $this->admin()->assertRedirect(route('profile.show'));

        session(['auth.password_confirmed_at' => time()]);
        $form = Livewire::test(TwoFactorAuthenticationForm::class)->call('enableTwoFactorAuthentication');
        $secret = Fortify::currentEncrypter()->decrypt($admin->refresh()->two_factor_secret);
        $form->set('code', $this->otp($secret))->call('confirmTwoFactorAuthentication')->assertHasNoErrors();

        $this->admin()->assertOk();
    }

    public function test_disabling_two_factor_closes_admin(): void
    {
        $admin = $this->verifiedSuperadmin($this->user());
        $this->actingAs($admin);
        session(['auth.password_confirmed_at' => time()]);

        Livewire::test(TwoFactorAuthenticationForm::class)->call('disableTwoFactorAuthentication');

        $this->admin()->assertRedirect(route('profile.show'));
    }

    // --- A session that has not seen a code ---

    public function test_a_session_without_a_code_is_asked_for_one(): void
    {
        $admin = $this->superadmin();
        $secret = $this->confirmTwoFactor($admin);
        $this->actingAs($admin);

        $this->admin()->assertRedirect(route('two-factor.verify'));
        Livewire::actingAs($admin)->test(Users::class)->assertForbidden();

        $this->get(route('two-factor.verify'))
            ->assertOk()
            ->assertSee(__('content.two_factor_verify_intro'));

        $this->post(route('two-factor.verify.store'), ['code' => $this->otp($secret)])
            ->assertRedirect(url('/admin/users'));
        $this->admin()->assertOk();
    }

    public function test_impersonation_asks_for_a_code_too(): void
    {
        $admin = $this->superadmin();
        $this->confirmTwoFactor($admin);
        $user = $this->user();

        $this->actingAs($admin)->get('/en/impersonate/take/' . $user->id)
            ->assertRedirect(route('two-factor.verify'));

        $this->assertSame($admin->id, auth()->id());
    }

    public function test_office_sign_in_does_not_open_admin_without_a_code(): void
    {
        config(['services.microsoft_office.client_id' => self::CLIENT_ID]);
        $admin = $this->superadmin();
        $this->confirmTwoFactor($admin);

        $this->post('/en/office-login', ['token' => $this->token(['email' => $admin->email, 'xms_edov' => true]), 'confirm' => 1]);
        $this->assertAuthenticatedAs($admin);

        $this->admin()->assertRedirect(route('two-factor.verify'));
    }

    public function test_a_correct_code_gives_the_session_a_new_id(): void
    {
        $admin = $this->superadmin();
        $secret = $this->confirmTwoFactor($admin);
        $this->actingAs($admin);
        $this->startSession();
        $before = session()->getId();

        // The browser's session, sent as its cookie, so the request continues it.
        $this->withCookie(config('session.cookie'), $before)
            ->post(route('two-factor.verify.store'), ['code' => $this->otp($secret)])
            ->assertRedirect(route('admin.home'));

        // A session id known before the code does not gain /admin.
        $this->assertNotSame($before, session()->getId());
    }

    public function test_the_check_refuses_a_wrong_code(): void
    {
        $admin = $this->superadmin();
        $this->confirmTwoFactor($admin);
        $this->actingAs($admin);

        $this->from(route('two-factor.verify'))
            ->post(route('two-factor.verify.store'), ['code' => '000000'])
            ->assertRedirect(route('two-factor.verify'))
            ->assertSessionHasErrors('code');

        $this->admin()->assertRedirect(route('two-factor.verify'));
    }

    public function test_the_check_takes_a_recovery_code_once(): void
    {
        $admin = $this->superadmin();
        $this->confirmTwoFactor($admin, ['recovery-code-one', 'recovery-code-two']);
        $this->actingAs($admin);

        $this->post(route('two-factor.verify.store'), ['recovery_code' => 'recovery-code-one'])->assertRedirect();
        $this->admin()->assertOk();
        $this->assertNotContains('recovery-code-one', $admin->refresh()->recoveryCodes());
    }

    public function test_the_check_is_rate_limited(): void
    {
        $admin = $this->superadmin();
        $this->confirmTwoFactor($admin);
        $this->actingAs($admin);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('two-factor.verify.store'), ['code' => '000000']);
        }

        $this->post(route('two-factor.verify.store'), ['code' => '000000'])->assertStatus(429);
    }

    public function test_a_superadmin_without_two_factor_is_sent_from_the_check_to_the_setup(): void
    {
        $this->actingAs($this->superadmin());

        $this->get(route('two-factor.verify'))->assertRedirect(route('profile.show'));
    }
}
