<?php

/*
|--------------------------------------------------------------------------
| Security Headers
|--------------------------------------------------------------------------
|
| App\Http\Middleware\SecurityHeaders adds these to every response. The
| fixed headers (X-Frame-Options, X-Content-Type-Options, Referrer-Policy,
| Permissions-Policy) and the policy's directives are set in the middleware;
| only the switches for rolling out the Content-Security-Policy are here.
|
| HSTS is not set by the app: the reverse proxy in front of it sets it.
|
*/

return [

    'csp' => [
        // Off only for local debugging, e.g. of Laravel's exception page.
        'enabled' => (bool) env('CSP_ENABLED', true),

        // Send the policy as Content-Security-Policy-Report-Only: browsers
        // report violations but block nothing. For a first rollout.
        'report_only' => (bool) env('CSP_REPORT_ONLY', false),

        // Where browsers send violation reports, for example Sentry's
        // security endpoint. Without it, violations show only in the
        // browser's console.
        'report_uri' => env('CSP_REPORT_URI'),
    ],

];
