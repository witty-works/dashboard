<?php

namespace App\Providers;

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

        $this->fixLocalizationRouteCommands();
    }

    /**
     * mcamara/laravel-localization names its route commands with `protected
     * $name`, but they extend Laravel's route commands, which since Laravel 13
     * carry an #[AsCommand] attribute. Symfony 8 walks the parent classes for
     * that attribute, so the package's commands get registered as `route:cache`
     * and `route:list` while still naming themselves `route:trans:*`. The result
     * is that `route:list` fails on a missing `locale` argument and
     * `route:trans:cache` — which the Platform.sh deploy hook runs — no longer
     * exists at all.
     *
     * The package resolves its commands out of the container by alias, so
     * pointing those aliases at subclasses that declare the attribute themselves
     * restores the intended names without touching the vendor directory.
     */
    protected function fixLocalizationRouteCommands(): void
    {
        $this->app->singleton(
            'laravellocalizationroutecache.cache',
            \App\Console\Localization\RouteTranslationsCacheCommand::class
        );

        $this->app->singleton(
            'laravellocalizationroutecache.list',
            \App\Console\Localization\RouteTranslationsListCommand::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('posthog.enabled')) {
            PostHog::init(
                config('posthog.api_key'),
                ['host' => config('posthog.host'), 'debug' => config('posthog.debug')],
            );
        }
    }
}
