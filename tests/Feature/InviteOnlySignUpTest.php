<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\Concerns\FakesOfficeTokens;
use Tests\TestCase;

/**
 * With INVITE_ONLY on, only an address with a pending invitation can get an
 * account, on the sign-up page and through Office sign-up from Word.
 */
class InviteOnlySignUpTest extends TestCase
{
    use FakesOfficeTokens;
    use RefreshDatabase;

    private const CLIENT_ID = 'office-client-id';

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        config([
            'auth.invite_only' => true,
            'services.microsoft_office.client_id' => self::CLIENT_ID,
            'services.microsoft_office.redirect_uri' => 'https://office.example/done',
        ]);
    }

    private function invite(string $email): void
    {
        $owner = User::factory()->withPersonalTeam()->create();
        $owner->ownedTeams()->first()->teamInvitations()->create(['email' => $email, 'role' => 'user']);
    }

    private function signUp(string $email)
    {
        return $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => $email,
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'terms' => true,
        ]);
    }

    private function officeRegister(string $email, string $path = '/en/office-register')
    {
        return $this->post($path, [
            'token' => $this->token(['email' => $email, 'xms_edov' => true, 'name' => 'Ada Lovelace']),
            'has_consented_to_terms_of_service' => 1,
            'has_consented_to_mailing' => 0,
        ]);
    }

    public function test_sign_up_without_an_invitation_is_refused(): void
    {
        $this->signUp('stranger@example.com')->assertSessionHasErrors('email');

        $this->assertNull(User::where('email', 'stranger@example.com')->first());
    }

    public function test_sign_up_with_an_invitation_works(): void
    {
        $this->invite('ada@example.com');

        $this->signUp('ada@example.com')->assertSessionHasNoErrors();

        $this->assertNotNull(User::where('email', 'ada@example.com')->first());
    }

    public function test_the_invitation_match_ignores_case(): void
    {
        $this->invite('Ada@Example.com');

        $this->signUp('ada@example.com')->assertSessionHasNoErrors();
    }

    public function test_office_sign_up_without_an_invitation_is_refused(): void
    {
        $this->officeRegister('stranger@example.com')
            ->assertRedirect('https://office.example/done?status=invite_only');

        $this->assertNull(User::where('email', 'stranger@example.com')->first());
    }

    public function test_office_login_of_an_unknown_address_is_refused_before_the_consent_form(): void
    {
        $this->post('/en/office-login', [
            'token' => $this->token(['email' => 'stranger@example.com', 'xms_edov' => true, 'name' => 'Ada']),
        ])->assertRedirect('https://office.example/done?status=invite_only');

        $this->assertNull(User::where('email', 'stranger@example.com')->first());
    }

    public function test_office_sign_up_with_an_invitation_works(): void
    {
        $this->invite('ada@example.com');

        $this->officeRegister('ada@example.com');

        $this->assertNotNull(User::where('email', 'ada@example.com')->first());
    }

    public function test_with_the_mode_off_sign_up_is_open(): void
    {
        config(['auth.invite_only' => false]);

        $this->signUp('stranger@example.com')->assertSessionHasNoErrors();
        $this->assertNotNull(User::where('email', 'stranger@example.com')->first());
    }
}
