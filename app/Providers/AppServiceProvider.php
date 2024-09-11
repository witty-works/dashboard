<?php

namespace App\Providers;

use App\Models\Subscription;
use App\Models\Team;
use Illuminate\Support\ServiceProvider;
use Firebase\JWT\JWT;
use Laravel\Cashier\Cashier;
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

            // Set CSP nonce for Laravel Debugbar during development
            if (class_exists(\Barryvdh\Debugbar\Facades\Debugbar::class) && app()->bound('debugbar')) {
                app('debugbar')->getJavascriptRenderer()->setCspNonce(csp_nonce());
            }
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
        Cashier::useCustomerModel(Team::class);
        Cashier::calculateTaxes();
        Cashier::useSubscriptionModel(Subscription::class);
        Cashier::keepPastDueSubscriptionsActive();

        if (config('posthog.enabled')) {
            PostHog::init(
                config('posthog.api_key'),
                ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
            );
        }
    }
}
