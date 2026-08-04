<?php

namespace Tests\Feature;

use App\Contracts\SocialAuth\CreatesConnectedAccounts;
use App\Contracts\SocialAuth\CreatesUserFromProvider;
use App\Contracts\SocialAuth\GeneratesProviderRedirect;
use App\Contracts\SocialAuth\HandlesInvalidState;
use App\Contracts\SocialAuth\ResolvesSocialiteUsers;
use App\Contracts\SocialAuth\SetsUserPasswords;
use App\Contracts\SocialAuth\UpdatesConnectedAccounts;
use App\Http\Controllers\OAuthController;
use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * joelbutcher/socialstream was archived upstream and removed; the pieces this
 * application used were absorbed into App\. These cover the seams that move
 * touched — container bindings that used to come from the package's static
 * registry, and the ConnectedAccount model that used to extend the package's.
 */
class SocialAuthWiringTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('contractProvider')]
    public function test_contract_resolves_from_the_container(string $contract, string $implementation): void
    {
        $this->assertInstanceOf($implementation, app($contract));
    }

    public static function contractProvider(): array
    {
        return [
            'resolves socialite users' => [ResolvesSocialiteUsers::class, \App\Actions\Socialstream\ResolveSocialiteUser::class],
            'creates user from provider' => [CreatesUserFromProvider::class, \App\Actions\Socialstream\CreateUserFromProvider::class],
            'creates connected accounts' => [CreatesConnectedAccounts::class, \App\Actions\Socialstream\CreateConnectedAccount::class],
            'updates connected accounts' => [UpdatesConnectedAccounts::class, \App\Actions\Socialstream\UpdateConnectedAccount::class],
            'sets user passwords' => [SetsUserPasswords::class, \App\Actions\Socialstream\SetUserPassword::class],
            'handles invalid state' => [HandlesInvalidState::class, \App\Actions\Socialstream\HandleInvalidState::class],
            'generates provider redirect' => [GeneratesProviderRedirect::class, \App\Actions\Socialstream\GenerateRedirectForProvider::class],
        ];
    }

    /**
     * The controller used to receive these three through the package's bindings.
     * If the container cannot build it, Office SSO is down.
     */
    public function test_oauth_controller_can_be_resolved(): void
    {
        $this->assertInstanceOf(OAuthController::class, app(OAuthController::class));
    }

    public function test_connected_account_belongs_to_a_user(): void
    {
        $user = User::factory()->create();

        $account = ConnectedAccount::forceCreate([
            'user_id' => $user->id,
            'provider' => 'microsoft_office',
            'provider_id' => 'provider-id-123',
            'email' => $user->email,
            'token' => 'a-token',
        ]);

        $this->assertTrue($account->user->is($user));
        $this->assertTrue($user->refresh()->connectedAccounts->contains($account));
    }

    public function test_user_can_look_up_a_connected_account_by_provider(): void
    {
        $user = User::factory()->create();

        ConnectedAccount::forceCreate([
            'user_id' => $user->id,
            'provider' => 'microsoft_office',
            'provider_id' => 'provider-id-123',
            'email' => $user->email,
            'token' => 'a-token',
        ]);

        $user->refresh();

        $this->assertTrue($user->hasTokenFor('microsoft_office'));
        $this->assertSame('a-token', $user->getTokenFor('microsoft_office'));
        $this->assertNotNull($user->getConnectedAccountFor('microsoft_office', 'provider-id-123'));
        $this->assertFalse($user->hasTokenFor('github'));
    }

    public function test_office_login_with_an_invalid_token_redirects_to_the_failure_url(): void
    {
        $this->withoutLocaleRedirects()
            ->get('/office-login?token=not-a-valid-jwt')
            ->assertRedirectContains('status=failed');
    }

    /**
     * The package registered generic oauth/{provider} routes. No provider was ever
     * configured, and they are gone with it.
     */
    public function test_generic_oauth_routes_are_no_longer_registered(): void
    {
        $this->assertNull(app('router')->getRoutes()->getByName('oauth.redirect'));
        $this->assertNull(app('router')->getRoutes()->getByName('oauth.callback'));
    }
}
