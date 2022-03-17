<?php

namespace App\Models;

use App\Http\Middleware\PostHogMiddleware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Spark\Billable;

class Team extends JetstreamTeam
{
    use HasFactory;
    use Billable;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'personal_team' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'personal_team',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => TeamCreated::class,
        'updated' => TeamUpdated::class,
        'saved' => TeamUpdated::class,
        'deleted' => TeamDeleted::class,
    ];

    public function stripeEmail()
    {
        return $this->owner->email;
    }

    /**
     * Get the current team of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organizationGuidelines()
    {
        return $this->hasOne(OrganizationGuidelines::class, 'team_id');
    }

    public function falsePositives()
    {
        return $this->hasMany(FalsePositive::class, 'team_id');
    }

    public function posthogId()
    {
        return PostHogMiddleware::POSTHOG_ID_PREFIX . $this->id;
    }

    public function maxUserCount()
    {
        return 3;
    }

    public function maxFalsePositiveCount()
    {
        return 5;
    }

    public function totalUserCount()
    {
        return $this->teamInvitations()->count() + $this->allUsers()->count();
    }
}
