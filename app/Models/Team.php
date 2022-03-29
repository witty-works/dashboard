<?php

namespace App\Models;

use App\Events\OrganizationGuidelinesUpdated;
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

    public function termReplacements()
    {
        return $this->hasMany(TermReplacement::class, 'team_id');
    }

    public function falsePositives()
    {
        return $this->hasMany(FalsePositive::class, 'team_id');
    }

    public function posthogId()
    {
        return PostHogMiddleware::POSTHOG_ID_PREFIX . $this->id;
    }

    public function getUserLicensesCountAttribute()
    {
        return $this->user_licenses ?? 3;
    }

    public function getTotalUserLicensesCountAttribute()
    {
        return $this->teamInvitations()->count() + $this->allUsers()->count();
    }

    public function getUserLicensesLimitReachedAttribute()
    {
        return $this->getTotalUserLicensesCountAttribute() >= $this->getUserLicensesCountAttribute();
    }

    public function getTermReplacementsCountAttribute()
    {
        return $this->term_replacements ?? 5;
    }

    public function getTotalTermReplacementsCountAttribute()
    {
        return $this->termReplacements()->count();
    }

    public function getTermReplacementsLimitReachedAttribute()
    {
        return $this->getTotalTermReplacementsCountAttribute() >= $this->getTermReplacementsCountAttribute();
    }

    public function getFalsePositivesCountAttribute()
    {
        return $this->false_positives ?? 5;
    }

    public function getTotalFalsePositivesCountAttribute()
    {
        return $this->falsePositives()->count();
    }

    public function getFalsePositivesLimitReachedAttribute()
    {
        return $this->getTotalFalsePositivesCountAttribute() >= $this->getFalsePositivesCountAttribute();
    }

    /**
     * Fire a custom model event for the given event.
     *
     * @param  string  $event
     * @param  string  $method
     * @return mixed|null
     */
    protected function fireCustomModelEvent($event, $method)
    {
        if (!in_array($event, ['saved', 'deleted', 'restored'])) {
            return;
        }

        $result = static::$dispatcher->$method(new OrganizationGuidelinesUpdated($this));
        if (!is_null($result)) {
            return $result;
        }
    }
}
