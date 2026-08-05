<?php

namespace App\Providers;

use App\Auth\AccessToken;
use App\Auth\ExtensionUserResolver;
use App\Models\ConnectedAccount;
use App\Models\OAuthClient;
use App\Models\Team;
use App\Policies\ConnectedAccountPolicy;
use App\Policies\TeamPolicy;
use DateInterval;
use RuntimeException;
use Throwable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        ConnectedAccount::class => ConnectedAccountPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        // Take over Passport's route registration; routes/oauth.php declares
        // what we actually serve. Two reasons:
        //
        //  - the packaged route file throttles POST /oauth/token with the bare
        //    `throttle` alias, i.e. 60/min, which is far too generous for a
        //    credential-issuing endpoint;
        //  - it also exposes /oauth/clients, /oauth/personal-access-tokens and
        //    /oauth/scopes, none of which this application has a UI for. They
        //    would be attack surface to secure for no benefit.
        //
        // This must happen in register(): Passport registers its routes from
        // boot(), and package providers boot before application ones.
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Passport::useClientModel(OAuthClient::class);

        // Adds a `kid` to the access token header. Without it no standard JWKS
        // client can verify our tokens — see App\Auth\AccessToken.
        Passport::useAccessTokenEntity(AccessToken::class);

        // Passport 13 ships no views and binds this contract nowhere by
        // default, so AuthorizationController cannot even be constructed
        // without it — /oauth/authorize 500s for every client, including the
        // first-party one that never renders a consent screen.
        Passport::authorizationView('oauth.authorize');

        // Laravel 13 stopped falling back to route('login') for an
        // AuthenticationException that carries no redirect — Handler::
        // unauthenticated() now answers with a bare 401 instead. The auth
        // middleware supplies its own redirect and is unaffected, but Passport's
        // AuthorizationController throws the exception directly, so a guest
        // following the extension's "Sign in" link would hit a 401 dead end
        // rather than the login page and back into the flow.
        AuthenticationException::redirectUsing(
            fn (Request $request) => $request->expectsJson() ? null : route('login')
        );

        Passport::tokensExpireIn($this->ttl('access_token_ttl', 'PT1H'));
        Passport::refreshTokensExpireIn($this->ttl('refresh_token_ttl', 'P30D'));

        // The browser extension and the Office add-in authenticate differently
        // but hit the same API routes. See App\Auth\ExtensionUserResolver.
        Auth::viaRequest('extension', function (Request $request) {
            return $this->app->make(ExtensionUserResolver::class)($request);
        });
    }

    /**
     * Read an ISO-8601 duration from config, falling back to the documented
     * default if it cannot be parsed.
     *
     * This runs in boot(), i.e. on every request, and DateInterval throws on a
     * malformed string. Letting that propagate would take the whole application
     * down — the login page included — because someone wrote "1H" instead of
     * "PT1H" in an env file. The fallback is the value documented in
     * .env.example, so a typo shortens nothing and lengthens nothing; it is
     * reported so the mistake still surfaces rather than living on silently.
     */
    protected function ttl(string $key, string $default): DateInterval
    {
        $value = (string) config("passport.{$key}", $default);

        try {
            return new DateInterval($value);
        } catch (Throwable $e) {
            report(new RuntimeException(
                "passport.{$key} is not a valid ISO-8601 duration (got \"{$value}\"); using {$default}.",
                previous: $e
            ));

            return new DateInterval($default);
        }
    }
}
