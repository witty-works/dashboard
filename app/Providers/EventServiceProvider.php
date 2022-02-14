<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\PosthogBillingEvent;
use App\Listeners\PosthogReset;
use App\Listeners\PostHogUpdateCompany;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Events\TeamMemberRemoved;
use Spark\Events\SubscriptionCreated;
use Spark\Events\SubscriptionUpdated;
use Spark\Events\SubscriptionCancelled;
use Spark\Events\PaymentSucceeded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        TeamMemberAdded::class => [
            PostHogUpdateCompany::class,
        ],
        TeamMemberAdded::class => [
            PostHogUpdateCompany::class,
        ],
        TeamMemberRemoved::class => [
            PostHogUpdateCompany::class,
        ],
        TeamCreated::class => [
            PostHogUpdateCompany::class,
        ],
        TeamUpdated::class => [
            PostHogUpdateCompany::class,
        ],
        TeamDeleted::class => [
            PostHogUpdateCompany::class,
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Logout::class => [
            PosthogReset::class,
        ],
        SubscriptionCreated::class => [
            PosthogBillingEvent::class,
        ],
        SubscriptionUpdated::class => [
            PosthogBillingEvent::class,
        ],
        SubscriptionCancelled::class => [
            PosthogBillingEvent::class,
        ],
        PaymentSucceeded::class => [
            PosthogBillingEvent::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            'SocialiteProviders\\AzureADB2C\\AzureADB2CExtendSocialite@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
