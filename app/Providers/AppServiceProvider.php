<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('language_switcher', function ($view) {
            $view->with('current_locale', app()->getLocale());
            $view->with('supported_locales', config('laravellocalization.supportedLocales'));
        });
    }
}
