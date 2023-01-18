<?php

namespace App\Listeners;

use App\Helpers\PosthogHelper;

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
            PosthogHelper::$posthog_reset = true;
        }
    }
}
