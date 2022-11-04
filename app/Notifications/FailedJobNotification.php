<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Cache;
use Spatie\FailedJobMonitor\Notification;

class FailedJobNotification
{
    public function notificationFilter(Notification $notification): bool
    {
        $cacheKey = 'failed-job-monitor.throttleing';
        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, 3600);

        return true;
    }
}