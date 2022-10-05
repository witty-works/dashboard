<?php

namespace App\Providers;

use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionUpdated;
use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Listeners\HubspotUpdateUser;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\PosthogBilling;
use App\Listeners\PosthogReset;
use App\Listeners\PostHogUpdateOrganization;
use App\Listeners\PostHogUpdateUser;
use App\Listeners\SyncStartRenwalDates;
use App\Listeners\TeamMemberAddedSlackAlert;
use App\Listeners\UpdateOrganizationGuidelines;
use App\Listeners\UpdateUserGuidelines;
use App\Listeners\UserAddedSlackAlert;
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
        UserCreated::class => [
            UpdateUserGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            UserAddedSlackAlert::class,
        ],
        UserDeleted::class => [
            UpdateUserGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
        ],
        UserUpdated::class => [
            UpdateUserGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
        ],
        TeamMemberAdded::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
            TeamMemberAddedSlackAlert::class,
        ],
        TeamMemberRemoved::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        TeamCreated::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        TeamUpdated::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        TeamDeleted::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        Logout::class => [
            PosthogReset::class,
        ],
        WebhookReceived::class => [
            PosthogBilling::class,
        ],
        SubscriptionCreated::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
            SyncStartRenwalDates::class,
        ],
        SubscriptionUpdated::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
            SyncStartRenwalDates::class,
        ],
        SubscriptionCancelled::class => [
            UpdateOrganizationGuidelines::class,
            HubspotUpdateUser::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
            SyncStartRenwalDates::class,
        ],
        SocialiteWasCalled::class => [
            AzureADB2CExtendSocialite::class,
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
