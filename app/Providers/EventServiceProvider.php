<?php

namespace App\Providers;

use App\Events\OrganizationGuidelinesUpdated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionUpdated;
use App\Events\UserCreated;
use App\Events\UserDeleted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\PosthogBilling;
use App\Listeners\PosthogReset;
use App\Listeners\PostHogUpdateCompany;
use App\Listeners\PostHogUpdateUser;
use App\Listeners\SyncStartRenwalDates;
use App\Listeners\TeamMemberAddedSlackAlert;
use App\Listeners\UpdateOrganizationGuidelines;
use App\Listeners\UpdateUserLicenses;
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
            UserAddedSlackAlert::class,
            PostHogUpdateUser::class,
        ],
        UserDeleted::class => [
            UserAddedSlackAlert::class,
            PostHogUpdateUser::class,
        ],
        UserUpdated::class => [
            PostHogUpdateUser::class,
        ],
        TeamMemberAdded::class => [
            PostHogUpdateCompany::class,
            PostHogUpdateUser::class,
            UpdateUserLicenses::class,
            UpdateOrganizationGuidelines::class,
            TeamMemberAddedSlackAlert::class,
        ],
        TeamMemberRemoved::class => [
            PostHogUpdateCompany::class,
            PostHogUpdateUser::class,
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
        Logout::class => [
            PosthogReset::class,
        ],
        WebhookReceived::class => [
            PosthogBilling::class,
        ],
        SubscriptionCreated::class => [
            PostHogUpdateCompany::class,
            SyncStartRenwalDates::class,
            UpdateOrganizationGuidelines::class,
            UpdateUserLicenses::class,
        ],
        SubscriptionUpdated::class => [
            PostHogUpdateCompany::class,
            SyncStartRenwalDates::class,
            UpdateOrganizationGuidelines::class,
            UpdateUserLicenses::class,
        ],
        SubscriptionCancelled::class => [
            PostHogUpdateCompany::class,
            SyncStartRenwalDates::class,
            UpdateOrganizationGuidelines::class,
            UpdateUserLicenses::class,
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
