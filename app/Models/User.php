<?php

namespace App\Models;

use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JoelButcher\Socialstream\HasConnectedAccounts;
use JoelButcher\Socialstream\SetsProfilePhotoFromUrl;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Jetstream\HasTeams;
use Laravel\Jetstream\Jetstream;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\Role;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto {
        getProfilePhotoUrlAttribute as getPhotoUrl;
    }
    use HasRoles;
    use Impersonate;
    use HasTeams;
    use HasConnectedAccounts;
    use Notifiable;
    use SetsProfilePhotoFromUrl;
    use TwoFactorAuthenticatable;
    use GuidelinesTrait;

    const ROLES = [
        '' => 'content.please_select',
        'executive' => 'content.executive',
        'lead' => 'content.lead',
        'employee' => 'content.employee'
    ];

    protected $emailProviders = [
        'gmail.com', 'bluewin.ch', 'icloud.com', 'hotmail.com', 'protonmail.com', 'protonmail.ch',
        'gmx.ch', 'gmx.at', 'gmx.de', 'gmx.net', 'mein.gmx', 'aol.com', 'outlook.com', 'zoho.com',
        'zohomail.eu', 'yahoo.com', 'web.de', 'cyon.ch', 'orange.fr', 'vodafone.com', 'me.com', 'hotmail.de',
        'hotmail.ch', 'yahoo.ch', 'yahoo.de', 'hey.com', 'hotmail.fr', 'bluemail.ch', 'freenet.de', 'sunrise.ch',
        'googlemail.com'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'team_analytics' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (filter_var($this->profile_photo_path, FILTER_VALIDATE_URL)) {
            return $this->profile_photo_path;
        }

        return $this->getPhotoUrl();
    }

    /**
     * Get the open invitiations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invitations()
    {
        return $this->hasMany(Jetstream::teamInvitationModel(), 'email', 'email');
    }
    /**
     * Get the current team of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentTeam()
    {
        return $this->belongsTo(Jetstream::teamModel(), 'current_team_id');
    }

    public function languageGuidelines()
    {
        return $this->hasOne(LanguageGuidelines::class, 'user_id');
    }

    public function termReplacements()
    {
        return $this->hasMany(TermReplacement::class, 'user_id');
    }

    public function falsePositives()
    {
        return $this->hasMany(FalsePositive::class, 'user_id');
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'user_id');
    }

    public function posthogId()
    {
        return config('posthog.dashboard_user_id_override', 'dashboard-user:' . $this->id);
    }

    public function posthogTeamId()
    {
        if ($this->currentTeam) {
            return $this->currentTeam->posthogId();
        }

        return null;
    }

    static public function getEmailFromProvider($userData)
    {
        return $userData['otherMails'][0]
            ?? $userData['emails'][0]
            ?? $userData['email']
            ?? null;
    }

    public function updateName($userData)
    {
        if (!empty($userData['name'])) {
            $this->name = $userData['name'];
        } elseif (!empty($userData['nickname'])) {
            $this->name = $userData['nickname'];
        }

        $email = self::getEmailFromProvider($userData);
        if (!empty($email)) {
            $this->email = $email;
        }
    }

    public function subscribed($name = 'teams', $price = null)
    {
        $team = $this->currentTeam;
        if (!$team) {
            return false;
        }

        return $team->subscribed($name, $price);
    }

    public function subscription($name = 'teams')
    {
        $team = $this->currentTeam;
        if (!$team) {
            return null;
        }

        return $team->subscription($name);
    }

    public function getTermReplacementsCount()
    {
        $key = '.features.user_term_replacements.count';
        if ($this->subscribed() && $this->term_replacements === null) {
            return config('stripe.plans.' . $this->planId() . $key);
        }

        return $this->term_replacements ?? config('stripe.plans.witty_free' . $key);
    }

    public function getFalsePositivesCount()
    {
        $key = '.features.user_false_positives.count';
        if ($this->subscribed() && $this->false_positives === null) {
            return config('stripe.plans.' . $this->planId() . $key);
        }

        return $this->false_positives ?? config('stripe.plans.witty_free' . $key);
    }

    public function planId()
    {
        $team = $this->currentTeam;
        if ($team) {
            return $team->planId();
        }

        return 'witty_free';
    }

    public function getNotificationCount()
    {
        $count = $this->invitations->count();
        if ($this->currentTeam && $this->ownsTeam($this->currentTeam)) {
            $count += $this->currentTeam->invitationRequests->count();
        }

        return $count;
    }

    public function getFirstNameAttribute()
    {
        $split = explode(' ', $this->name);
        if (count($split) <= 1) {
            return $this->name;
        }

        array_pop($split);

        return implode(' ', $split);
    }

    public function getLastNameAttribute()
    {
        $split = explode(' ', $this->name);
        if (count($split) <= 1) {
            return '';
        }

        return array_pop($split);
    }

    public function getEmailDomain()
    {
        $email = explode('@', $this->email);
        return array_pop($email);
    }

    public function isSharedEmailAccount()
    {
        $provider = $this->getEmailDomain();

        return in_array($provider, $this->emailProviders);
    }

    public function hasCompletedOnboarding()
    {
        if (
            $this->role !== null
            || !$this->currentTeam
            || (config('app.no_onboarding_for_impersonation') && app('impersonate')->isImpersonating())
        ) {
            return true;
        }

        return !$this->ownsTeam($this->currentTeam);
    }

    public function getTeamRoleName()
    {
        if (!$this->currentTeam) {
            return null;
        }

        $teamRole = $this->teamRole($this->currentTeam);
        if ($teamRole instanceof Role) {
            return $teamRole->name;
        }

        return null;
    }

    public function getWritingStreakPast30Days()
    {
        return Kpi::getWritingStreakPast30Days($this);
    }

    public function getCompanyUsers()
    {
        return User::where('email', '!=', $this->email)
            ->where('id', '!=', $this->id)
            ->where('current_team_id', '!=', $this->team_id)
            ->where('email', 'LIKE', '%@' . $this->getEmailDomain())
            ->get();
    }

    public function getHubspotCompanyUserCount()
    {
        return self::where('hubspot_company_id', $this->hubspot_company_id)->count();
    }

    public function getHubspotData($booleanAsStrings = false)
    {
        $true = $booleanAsStrings ? 'Yes' : true;
        $false = $booleanAsStrings ? 'No' : false;

        $data = [
            'witty_account_created_at' => $this->created_at->__toString(),
            'user_role' => $this->role,
            'how_did_you_find' => $this->found,
            'has_witty_account' => $true,
            'has_consented_to_mailing' => $this->has_consented_to_mailing ? $true : $false,
            'has_accessed_stripe' => $this->has_accessed_stripe ? $true : $false,
            'witty_plan' => $this->planId(),
            'hs_language' => $this->language,
            'dashboard_id' => $this->posthogId(),
            'team_dashboard_id' => $this->posthogTeamId(),
            'impersonate_url' => config('app.url') . '/impersonate/take/' . $this->id,
            'dictionary_count' => $this->termReplacements->count(),
            'ignore_count' => $this->falsePositives->count(),
            'has_team_language_rules' => $false,
            'has_team_privacy_set' => $false,
            'team_role' => $this->getTeamRoleName(),
            'invited_team_member_count' => 0,
            'team_member_count' => 0,
            'team_dictionary_count' => 0,
            'team_ignore_count' => 0,
            'hubspot_company_user_count' => $this->hubspot_company_id ? $this->getHubspotCompanyUserCount() : null,
            'writing_streak' => $this->getWritingStreakPast30Days(),
        ];

        if (!empty($this->company_name)) {
            $data['company'] = $this->company_name;
        }

        if ($this->currentTeam && $this->ownsTeam($this->currentTeam)) {
            $data['has_team_language_rules'] = $this->currentTeam->hasLanguageRules() ? $true : $false;
            $data['has_team_privacy_set'] = $this->currentTeam->hasConfiguredPrivacy() ? $true : $false;
            $data['team_dictionary_count'] = $this->currentTeam->termReplacements->count();
            $data['team_ignore_count'] = $this->currentTeam->falsePositives->count();
            $data['invited_team_member_count'] = $this->currentTeam->teamInvitations()->count();
            $data['team_member_count'] = $this->currentTeam->getTotalUserCount();
            $data['team_writing_streak'] = $this->currentTeam->getWritingStreakPast30Days();
        }

        return $data;
    }

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => UserCreated::class,
        'updated' => UserUpdated::class,
        'deleted' => UserDeleted::class,
    ];
}
