<?php

namespace App\Providers;

use App\Helpers\PosthogHelper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Firebase\JWT\JWT;
use PostHog\PostHog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        JWT::$leeway = 10;

        // Here rather than in EventServiceProvider: listeners attached in a
        // provider's boot() run in provider order, and laravel-impersonate's
        // Logout listener forgets whom the session is impersonating. Every
        // register() runs before any boot(), so this one sees it still.
        $this->app['events']->listen(\Illuminate\Auth\Events\Logout::class, \App\Listeners\RevokeOAuthTokens::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // After Jetstream's own registration (JetstreamServiceProvider::boot).
        $this->app->booted(function () {
            \Livewire\Livewire::component('profile.two-factor-authentication-form', \App\Livewire\Profile\TwoFactorAuthenticationForm::class);
        });

        // <script @cspNonce>: the nonce App\Http\Middleware\SecurityHeaders
        // allows inline scripts with. Nothing while the policy is off.
        Blade::directive('cspNonce', fn () => '<?php if ($__cspNonce = \\Illuminate\\Support\\Facades\\Vite::cspNonce()) { echo \'nonce="\' . e($__cspNonce) . \'"\'; } ?>');

        if (config('posthog.enabled')) {
            PosthogHelper::init();
        }
    }
}
