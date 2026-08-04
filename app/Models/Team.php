<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Laravel\Jetstream\Events\TeamCreated;
use Laravel\Jetstream\Events\TeamDeleted;
use Laravel\Jetstream\Events\TeamUpdated;
use Laravel\Jetstream\Team as JetstreamTeam;

class Team extends JetstreamTeam
{
    use HasFactory;
    use GuidelinesTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'personal_team' => 'boolean',
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

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strip_tags($name);
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

    public function getWritingStreakPast30Days()
    {
        return Kpi::getWritingStreakPast30Days($this);
    }
}
