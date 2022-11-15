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
    public const POSTHOG_ORGANIZATION_TYPE = 'organization';

    public static $posthog_reset = false;

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

        Cashier::ignoreMigrations();
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

        if (config('posthog.enabled')) {
            PostHog::init(
                config('posthog.api_key'),
                ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
            );
        }
    }
}
