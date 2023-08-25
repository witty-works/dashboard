<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MissingAttributeException;
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

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strip_tags($name);
    }

    public function subscribed($name = 'teams', $price = null)
    {
        if ($name === 'teams' && $price === null) {
            $price = config('stripe.plans.witty_teams.price_id');
        }

        return $this->parentSubscribed($name, $price);
    }

    public function subscription($name = 'teams')
    {
        return $this->parentSubscription($name);
    }

    public function stripeEmail()
    {
        return $this->owner->email;
    }

    public function stripeName()
    {
        return $this->owner->name;
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

    public function invitationRequests()
    {
        return $this->hasMany(TeamInvitationRequest::class, 'team_id');
    }

    public function posthogId()
    {
        return config('posthog.dashboard_team_id_override')
            ?? 'dashboard-team:' . $this->id;
    }

    public function getUserLicensesCount()
    {
        if ($this->subscribed()) {
            return $this->subscription()->quantity;
        }

        return $this->user_licenses ?? config('stripe.plans.witty_free.features.invite_smaller_teams.count');
    }

    public function allUsers()
    {
        if ($this->owner === null) {
            throw new MissingAttributeException($this, 'owner');
        }

        return parent::allUsers();
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

    public function hasLanguageRules()
    {
        return $this->languageGuidelines !== null && $this->languageGuidelines->customized;
    }

    public function hasConfiguredPrivacy()
    {
        if (
            $this->languageGuidelines
            && $this->languageGuidelines->domain_list_type === 'allow_witty_works'
        ) {
            return true;
        }

        return (bool) $this->domains()->count();
    }

    public function redirectToCheckout($licenseCount = null)
    {
        $this->owner->has_accessed_stripe = true;
        $this->owner->save();

        $subscriptionRoute = route('teams.subscription');
        if ($this->subscribed() && !$this->subscription()->canceled()) {
            return false;
        }

        return $this
            ->allowPromotionCodes()
            ->checkout(
                [[
                    'price' => config('stripe.plans.witty_teams.price_id'),
                    'quantity' => $licenseCount ?? $this->getTotalUserWithInvitationsCount()
                ]],
                [
                    'success_url' => $subscriptionRoute,
                    'cancel_url' => $subscriptionRoute,
                    'mode' => 'subscription'
                ]
            )->redirect();
    }

    public function getWritingStreakPast30Days()
    {
        return Kpi::getWritingStreakPast30Days($this);
    }
}
