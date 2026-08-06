<?php

use App\Http\Controllers\JwksController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Well-Known URIs
|--------------------------------------------------------------------------
|
| Registered from RouteServiceProvider without the `web` middleware: these are
| unauthenticated machine-to-machine GETs, so a session, a CSRF token and a
| locale prefix would all be wrong. RFC 8615 fixes the paths, so they must sit
| at the domain root rather than under /en, /de or /fr.
|
*/

// Verification keys for the RS256 access tokens issued by /oauth/token. The NLP
// API fetches this to validate extension tokens, the same way it already
// fetches Microsoft's keys for Office SSO tokens.
Route::get('/.well-known/jwks.json', JwksController::class)
    ->middleware('throttle:api')
    ->name('jwks');
