<?php

namespace App\Models;

use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Http\Controllers\OAuthController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\HasConnectedAccounts;
use Laravel\Fortify\TwoFactorAuthenticatable;
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
    use HasRoles;
    use Impersonate;
    use HasTeams;
    use HasConnectedAccounts;
    use Notifiable;
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

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strip_tags($name);
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

    public function allSwitchableTeams()
    {
        $teams = $this->allTeams();

        if ($teams->count() === 1) {
            return [];
        }

        $switchableTeams = [];
        foreach ($teams as $team) {
            if ($team->getTotalUserWithInvitationsCount() > 1 || $team->id === $this->license_team_id) {
                $switchableTeams[] = $team;
            }
        }

        return $switchableTeams;
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
        return config('posthog.dashboard_user_id_override')
            ?? 'dashboard-user:' . $this->id;
    }

    public function posthogTeamId()
    {
        if ($this->licenseTeam) {
            return $this->licenseTeam->posthogId();
        }

        return null;
    }

    public static function getEmailFromProvider($userData)
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

    public function licenseTeam()
    {
        return $this->belongsTo(Jetstream::teamModel(), 'license_team_id');
    }

    public function isUserLicensedToTeam($team = null)
    {
        if ($this->licenseTeam === null) {
            return false;
        }

        if ($team === null) {
            $team = $this->currentTeam;
        }

        return $this->licenseTeam->id === $team->id;
    }

    public function subscribed($type = 'witty', $price = null)
    {
        $team = $this->currentTeam;
        if (!$team) {
            return false;
        }

        return $team->subscribed($type, $price);
    }

    public function subscription($type = 'witty')
    {
        $team = $this->currentTeam;
        if (!$team) {
            return null;
        }

        return $team->subscription($type);
    }

    public function getTermReplacementsCount()
    {
        $key = '.features.user_term_replacements.count';
        if ($this->subscribed() && $this->term_replacements === null) {
            return config('stripe.plans.' . $this->currentTeam->planId() . $key);
        }

        return $this->term_replacements ?? config('stripe.plans.witty_free' . $key);
    }

    public function getFalsePositivesCount()
    {
        $key = '.features.user_false_positives.count';
        if ($this->subscribed() && $this->false_positives === null) {
            return config('stripe.plans.' . $this->currentTeam->planId() . $key);
        }

        return $this->false_positives ?? config('stripe.plans.witty_free' . $key);
    }

    public function planId()
    {
        $team = $this->licenseTeam;
        if ($team) {
            return $team->planId();
        }

        return "none";
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

    public function getOrganizationUsers($owners = false, $subscribed = false)
    {
        $query = User::where('users.email', '!=', $this->email)
            ->where('users.id', '!=', $this->id)
            ->where('users.current_team_id', '!=', $this->current_team_id)
            ->where('users.email', 'LIKE', '%@' . $this->getEmailDomain());

        if ($owners) {
            $query->join('teams', 'users.current_team_id', '=', 'teams.id')
                ->whereColumn('users.id', 'teams.user_id');
        }

        if ($subscribed) {
            $query->join('subscriptions', 'subscriptions.team_id', '=', 'users.current_team_id');
        }

        return $query->get();
    }

    public function getHubspotCompanyUserCount()
    {
        return self::where('hubspot_company_id', $this->hubspot_company_id)->count();
    }

    protected function getUserClients()
    {
        $clients = [];
        foreach ($this->connectedAccounts as $connectedAccount) {
            if ($connectedAccount->provider == OAuthController::AZURE_AD_B2C_PROVIDER) {
                $clients[] = 'browser';
            } elseif ($connectedAccount->provider == OAuthController::OFFICE_PROVIDER) {
                $clients[] = 'word';
            }
        }

        return array_unique($clients);
    }

    public function getSignupSource()
    {
        $provider = DB::select("SELECT provider FROM connected_accounts where email = ? ORDER BY id ASC", [$this->email]);
        return empty($provider[0]->provider) ? 'unknown' : $provider[0]->provider;
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
            'witty_plan' => $this->planId() ?? 'unlicensed',
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
            'clients' => implode(';', $this->getUserClients()),
        ];

        if (!empty($this->company_name)) {
            $data['company'] = $this->company_name;
        }

        // Users may own multiple teams, this code here does not handle this
        // so in that case it will simply sync the data of the team they most recently used
        $personalTeam = $this->personalTeam();
        if ($personalTeam) {
            $data['has_team_language_rules'] = $personalTeam->hasLanguageRules() ? $true : $false;
            $data['has_team_privacy_set'] = $personalTeam->hasConfiguredPrivacy() ? $true : $false;
            $data['team_dictionary_count'] = $personalTeam->termReplacements->count();
            $data['team_ignore_count'] = $personalTeam->falsePositives->count();
            $data['invited_team_member_count'] = $personalTeam->teamInvitations()->count();
            $data['team_member_count'] = $personalTeam->getTotalUserCount();
            $data['team_writing_streak'] = $personalTeam->getWritingStreakPast30Days();
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
