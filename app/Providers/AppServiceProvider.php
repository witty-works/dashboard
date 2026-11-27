<?php

namespace App\Providers;

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
            PostHog::init(
                config('posthog.api_key'),
                ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
            );
        }
    }
}
