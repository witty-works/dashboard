<?php

namespace App\Models;

use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use GuidelinesTrait;

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

    public function languageGuidelines()
    {
        return $this->hasOne(LanguageGuidelines::class, 'team_id');
    }

    public function termReplacements()
    {
        return $this->hasMany(TermReplacement::class, 'team_id');
    }

    public function falsePositives()
    {
        return $this->hasMany(FalsePositive::class, 'team_id');
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'team_id');
    }

    public function posthogId()
    {
        return AppServiceProvider::POSTHOG_ID_PREFIX . $this->id;
    }

    public function getUserLicensesCount()
    {
        if ($this->subscribed()) {
            return $this->subscription()->quantity;
        }

        return $this->user_licenses ?? config('stripe.plans.witty_free.features.invite_smaller_teams.count');
    }

    public function getTotalUserCount()
    {
        return $this->allUsers()->count();
    }

    public function getTotalUserWithInvitationsCount()
    {
        return $this->getTotalUserCount() + $this->teamInvitations()->count();
    }

    public function getUserLicensesLimitReached()
    {
        if ($this->subscribed() && !$this->subscription()->isPaidByInvoice()) {
            return false;
        }

        return $this->getTotalUserWithInvitationsCount() >= $this->getUserLicensesCount();
    }

    public function getTermReplacementsCount()
    {
        $key = '.features.organization_term_replacements.count';
        if ($this->subscribed() && $this->term_replacements === null) {
            return config('stripe.plans.' . $this->planId() . $key);
        }

        return $this->term_replacements ?? config('stripe.plans.witty_free' . $key);
    }

    public function getFalsePositivesCount()
    {
        $key = '.features.organization_false_positives.count';
        if ($this->subscribed() && $this->false_positives === null) {
            return config('stripe.plans.' . $this->planId() . $key);
        }

        return $this->false_positives ?? config('stripe.plans.witty_free' . $key);
    }

    public function planId()
    {
        if (!$this->subscribed()) {
            return 'witty_free';
        }

        return $this->subscription()->planId();
    }

    public function getDomainListType()
    {
        if (!$this->languageGuidelines) {
            return 'deny';
        }

        return $this->languageGuidelines->domain_list_type;
    }
}
