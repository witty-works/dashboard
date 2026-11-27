<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Registered first so nothing declared later can shadow it. These
            // are the Passport routes, taken over from the package — see
            // App\Providers\AuthServiceProvider::register().
            Route::prefix('oauth')
                ->as('passport.')
                ->group(base_path('routes/oauth.php'));

            // RFC 8615 fixes these paths at the domain root, so they must be
            // registered before the localized groups in routes/web.php.
            Route::group([], base_path('routes/wellknown.php'));

            // /up, for the container and the proxy; no session, no locale.
            Route::group([], base_path('routes/health.php'));

            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // Credential-issuing endpoints are brute-force targets and are hit far
        // less often than the general API, so they get their own, much tighter
        // budget. Keyed on IP only: these requests are by definition made before
        // a session exists, so `$request->user()` is null and keying on it would
        // collapse every caller into one bucket.
        // POST /oauth/token. The write page's client gets a limit of its own:
        // it exchanges a code on every page load and refreshes about hourly
        // per open tab, which colleagues behind one office IP would otherwise
        // spend together with the extension's sign-ins. Codes and refresh
        // tokens cannot be guessed, so a separate limit costs no security.
        // Both are deployment settings (config/passport.php).
        RateLimiter::for('auth', function (Request $request) {
            $editorClient = config('passport.editor.client_id');
            if ($editorClient && $request->input('client_id') === (string) $editorClient) {
                return Limit::perMinute((int) config('passport.editor.token_rate_limit'))
                    ->by('editor|' . $request->ip());
            }

            return Limit::perMinute((int) config('passport.token_rate_limit'))->by($request->ip());
        });
    }
}
