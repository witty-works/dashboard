<?php

namespace App\Providers;

use App\Actions\SocialAuth\CreateConnectedAccount;
use App\Actions\SocialAuth\CreateUserFromProvider;
use App\Actions\SocialAuth\GenerateRedirectForProvider;
use App\Actions\SocialAuth\HandleInvalidState;
use App\Actions\SocialAuth\ResolveSocialiteUser;
use App\Actions\SocialAuth\SetUserPassword;
use App\Actions\SocialAuth\UpdateConnectedAccount;
use App\Contracts\SocialAuth\CreatesConnectedAccounts;
use App\Contracts\SocialAuth\CreatesUserFromProvider;
use App\Contracts\SocialAuth\GeneratesProviderRedirect;
use App\Contracts\SocialAuth\HandlesInvalidState;
use App\Contracts\SocialAuth\ResolvesSocialiteUsers;
use App\Contracts\SocialAuth\SetsUserPasswords;
use App\Contracts\SocialAuth\UpdatesConnectedAccounts;
use Illuminate\Support\ServiceProvider;

/**
 * These bindings used to go through joelbutcher/socialstream's static registry
 * (Socialstream::createUsersFromProviderUsing(...) and friends). That package was
 * archived upstream in December 2025 and never supported Laravel 13, so the
 * actions are now bound to application-owned contracts in the container instead.
 *
 * OAuthController resolves CreatesUserFromProvider, CreatesConnectedAccounts and
 * UpdatesConnectedAccounts from here for the Microsoft Office SSO flow.
 */
class SocialAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ResolvesSocialiteUsers::class, ResolveSocialiteUser::class);
        $this->app->singleton(CreatesUserFromProvider::class, CreateUserFromProvider::class);
        $this->app->singleton(CreatesConnectedAccounts::class, CreateConnectedAccount::class);
        $this->app->singleton(UpdatesConnectedAccounts::class, UpdateConnectedAccount::class);
        $this->app->singleton(SetsUserPasswords::class, SetUserPassword::class);
        $this->app->singleton(HandlesInvalidState::class, HandleInvalidState::class);
        $this->app->singleton(GeneratesProviderRedirect::class, GenerateRedirectForProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
