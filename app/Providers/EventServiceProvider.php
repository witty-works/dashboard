<?php

namespace App\Providers;

use App\Events\OrganizationGuidelinesUpdated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionUpdated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\PosthogBilling;
use App\Listeners\PosthogReset;
use App\Listeners\PostHogUpdateCompany;
use App\Listeners\SyncStartRenwalDates;
use App\Listeners\UpdateOrganizationGuidelines;
use App\Listeners\UpdateUserLicenses;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Events\TeamMemberRemoved;
use Laravel\Cashier\Events\WebhookReceived;
use SocialiteProviders\AzureADB2C\AzureADB2CExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
            UpdateUserLicenses::class,
            UpdateOrganizationGuidelines::class,
        ],
        TeamMemberRemoved::class => [
            PostHogUpdateCompany::class,
            UpdateUserLicenses::class,
            UpdateOrganizationGuidelines::class,
        ],
        TeamCreated::class => [
            PostHogUpdateCompany::class,
            UpdateOrganizationGuidelines::class,
        ],
        TeamUpdated::class => [
            PostHogUpdateCompany::class,
            UpdateOrganizationGuidelines::class,
        ],
        TeamDeleted::class => [
            PostHogUpdateCompany::class,
            UpdateUserLicenses::class,
            UpdateOrganizationGuidelines::class,
        ],
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Logout::class => [
            PosthogReset::class,
        ],
        WebhookReceived::class => [
            PosthogBilling::class,
        ],
        SubscriptionCreated::class => [
            SyncStartRenwalDates::class,
            UpdateOrganizationGuidelines::class,
        ],
        SubscriptionUpdated::class => [
            SyncStartRenwalDates::class,
            UpdateOrganizationGuidelines::class,
        ],
        SubscriptionCancelled::class => [
            SyncStartRenwalDates::class,
            UpdateOrganizationGuidelines::class,
        ],
        SocialiteWasCalled::class => [
            AzureADB2CExtendSocialite::class,
        ],
        OrganizationGuidelinesUpdated::class => [
            UpdateOrganizationGuidelines::class,
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
