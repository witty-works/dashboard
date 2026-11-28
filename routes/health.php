<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Health Check
|--------------------------------------------------------------------------
|
| For the container's HEALTHCHECK, the reverse proxy and external probes.
| Registered from RouteServiceProvider without the `web` middleware: with the
| database session driver every request through `web` writes a session row,
| and a probe every few seconds would fill the table.
|
| 200 when the application boots and the database answers, 503 otherwise.
| The body says only which; the reason goes to the log.
|
*/

Route::get('/up', function () {
    try {
        DB::select('select 1');
    } catch (Throwable $e) {
        Log::error('Health check: database unreachable: ' . $e->getMessage());

        return response('database unreachable', 503)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'no-store');
    }

    return response('OK', 200)
        ->header('Content-Type', 'text/plain')
        ->header('Cache-Control', 'no-store');
})->name('health');
