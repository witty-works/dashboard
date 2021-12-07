<?php

namespace App\Listeners;

use App\Http\Middleware\PostHogMiddleware;

class PosthogReset
{

    /**
     * Handle the event.
     *
     * @param Illuminate\Auth\Events\Registered|Illuminate\Auth\Events\Login $event
     * @return void
     */
    public function handle($event)
    {
        if (config('posthog.enabled')) {
            PostHogMiddleware::$reset = true;
        }
    }
}
