<?php

namespace App\Models;

use App\Events\OrganizationGuidelinesUpdated;
use App\Http\Middleware\PostHogMiddleware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Gate;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;
use Laravel\Cashier\Billable;

class Team extends JetstreamTeam
{
    use HasFactory;
    use Billable {
        subscribed as protected parentSubscribed;
        subscription as protected parentSubscription;
    }

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
        'stripe_id',
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

    public function subscribed($name = 'witty', $price = null)
    {
        if ($name === 'witty' && $price === null) {
            $price = config('stripe.plans.witty_teams.price_id');
        }

        return $this->parentSubscribed($name, $price);
    }

    public function subscription($name = 'witty')
    {
        return $this->parentSubscription($name);
    }

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
        if ($this->subscribed()) {
            return $this->subscription()->quantity;
        }

        return config('stripe.plans.witty_me.features.invite_smaller_teams.count');
    }

    public function getTotalUserLicensesCountAttribute()
    {
        return $this->allUsers()->count();
    }

    public function getUserLicensesLimitReachedAttribute()
    {
        if ($this->subscribed() && !$this->subscription()->isPaidByInvoice()) {
            return false;
        }

        return $this->getTotalUserLicensesCountAttribute() >= $this->getUserLicensesCountAttribute();
    }

    public function getTermReplacementsCountAttribute()
    {
        if ($this->subscribed() && $this->term_replacements === null) {
            return config('stripe.plans.' . $this->subscription()->planId() . '.features.term_replacements.count');
        }

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
        if ($this->subscribed() && $this->false_positives === null) {
            return config('stripe.plans.' . $this->subscription()->planId() . '.features.false_positives.count');
        }

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

    public function getStoreContextDisablableAttribute()
    {
        return $this->subscribed() && Gate::check('update', $this);
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
