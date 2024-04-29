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
    use Billable;
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

        return config('stripe.plans.' . $this->planId(true) . '.features.invite_smaller_teams.count');
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

    public function userLicenses()
    {
        return $this->allUsers()->where('license_team_id', $this->id);
    }

    public function getUserLicensesLimitReached($invitations = false)
    {
        $count = $this->userLicenses()->count();
        if ($invitations) {
            $count += $this->teamInvitations()->count();
        }

        return $count >= $this->getUserLicensesCount();
    }

    public function getTermReplacementsCount()
    {
        $key = '.features.organization_term_replacements.count';
        return config('stripe.plans.' . $this->planId(true) . $key);
    }

    public function getFalsePositivesCount()
    {
        $key = '.features.organization_false_positives.count';
        return config('stripe.plans.' . $this->planId(true) . $key);
    }

    public function planId($featurePlan = false)
    {
        $subscription = $this->subscription();
        if ($subscription) {
            $this->subscription()->planId();
        }

        if ($this->onGenericTrial()) {
            // Teams on the trial get "Witty Teams" features
            return $featurePlan ? 'witty_teams' : 'witty_free';
        }

        return ($this->trial_ends_at === null || $featurePlan) ? 'witty_free' : null;
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
