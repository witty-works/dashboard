<?php

namespace App\Listeners;

class PosthogReset
{
    public static $posthog_reset = false;

    /**
     * Handle the event.
     *
     * @param Illuminate\Auth\Events\Registered|Illuminate\Auth\Events\Login $event
     * @return void
     */
    public function handle($event)
    {
        if (config('posthog.js_enabled')) {
            self::$posthog_reset = true;
        }
    }
}
