<?php

namespace App\Listeners;

use App\Providers\AppServiceProvider;

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
        if (config('posthog.js_enabled')) {
            AppServiceProvider::$posthog_reset = true;
        }
    }
}
