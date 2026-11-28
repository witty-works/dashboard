<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Cache;
use Spatie\FailedJobMonitor\Notification;

class FailedJobNotification
{
    public static function notificationFilter(Notification $notification): bool
    {
        // Nobody to tell. A mail to an empty address would throw inside the
        // JobFailed listener, and the worker would not record the job in
        // failed_jobs.
        if (empty(config('failed-job-monitor.mail.to'))) {
            return false;
        }

        $cacheKey = 'failed-job-monitor.throttleing';
        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, 3600);

        return true;
    }
}
