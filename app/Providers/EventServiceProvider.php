<?php

namespace App\Providers;

use App\Events\InvitedTeamMember;
use App\Events\UserCompanyUpdated;
use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Listeners\PosthogReset;
use App\Listeners\PostHogUpdateOrganization;
use App\Listeners\PostHogUpdateUser;
use App\Listeners\TeamMemberAddedSlackAlert;
use App\Listeners\TeamMemberInvitedSlackAlert;
use App\Listeners\UpdateCurrentTeam;
use App\Listeners\UpdateOrganizationGuidelines;
use App\Listeners\UpdateUserGuidelines;
use App\Listeners\UserAddedSlackAlert;
use Illuminate\Auth\Events\Logout;
use App\Listeners\UserCompanyUpdatedSlackAlert;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamMemberAdded;
use Laravel\Jetstream\Events\TeamMemberRemoved;

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
            UserAddedSlackAlert::class,
        ],
        UserDeleted::class => [
            UpdateUserGuidelines::class,
            PostHogUpdateUser::class,
        ],
        UserUpdated::class => [
            UpdateUserGuidelines::class,
            PostHogUpdateUser::class,
        ],
        UserCompanyUpdated::class => [
            UserCompanyUpdatedSlackAlert::class,
        ],
        InvitedTeamMember::class => [
            PostHogUpdateUser::class,
            TeamMemberInvitedSlackAlert::class,
        ],
        TeamMemberAdded::class => [
            UpdateOrganizationGuidelines::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
            TeamMemberAddedSlackAlert::class,
        ],
        TeamMemberRemoved::class => [
            UpdateCurrentTeam::class,
            UpdateOrganizationGuidelines::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        TeamCreated::class => [
            UpdateOrganizationGuidelines::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        TeamUpdated::class => [
            UpdateOrganizationGuidelines::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        TeamDeleted::class => [
            UpdateOrganizationGuidelines::class,
            PostHogUpdateUser::class,
            PostHogUpdateOrganization::class,
        ],
        Logout::class => [
            PosthogReset::class,
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
