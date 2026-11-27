<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use App\Actions\Jetstream\HandOverInvitedTeam;
use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Http\Controllers\OAuthController;
use App\Jobs\SyncUserToNlpApi;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\HasConnectedAccounts;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Jetstream\Contracts\AddsTeamMembers;
use Laravel\Jetstream\HasTeams;
use Laravel\Jetstream\Jetstream;
use Laravel\Passport\HasApiTokens;
use Laravel\Jetstream\Role;

class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    // Passport's trait, not Sanctum's. Sanctum is still the guard for the
    // session-backed SPA requests, but it only reaches for this trait when a
    // bearer token is present (see Laravel\Sanctum\Guard::supportsTokens), and
    // nothing in the application ever issued a Sanctum personal access token —
    // Jetstream's api feature is disabled in config/jetstream.php. Bearer
    // tokens are Passport's job now.
    use HasApiTokens;
    use HasFactory;
    use Impersonate;
    use HasConnectedAccounts;

    /**
     * spatie/laravel-permission 8 added HasRoles::teams(), which collides with
     * Jetstream's. Its own teams feature is not enabled here (config/permission.php
     * sets no 'teams' key), so that method is a stub that deliberately returns no
     * rows — Jetstream's real relation wins.
     */
    use HasRoles, HasTeams {
        HasTeams::teams insteadof HasRoles;
    }

    use Notifiable;
    use TwoFactorAuthenticatable;
    use GuidelinesTrait;

    const ROLES = [
        '' => 'content.please_select',
        'executive' => 'content.executive',
        'lead' => 'content.lead',
        'employee' => 'content.employee'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        'two_factor_confirmed_at' => 'datetime',
        'team_analytics' => 'boolean',
    ];

    /**
     * The language mails and notifications to this person are written in:
     * the one they last used the dashboard in, also when the mail is sent
     * from the queue. Null leaves the current language.
     */
    public function preferredLocale(): ?string
    {
        $language = $this->language;

        return in_array($language, config('localizer.supported_locales'), true) ? $language : null;
    }

    /**
     * Revoke every OAuth access and refresh token of this user: the browser
     * extension and the write page are signed out. On logout and when the
     * password changes.
     *
     * Through the models rather than a repository: Passport 13 deleted
     * RefreshTokenRepository and reduced TokenRepository to lookups.
     *
     * Refresh tokens are matched against *every* access token this user has,
     * not just the unrevoked ones. league/oauth2-server's refresh grant does
     * not look at the access token's revoked flag, so a refresh token whose
     * access token was revoked by some other path still mints new access
     * tokens for its full 30 days.
     *
     * Two bulk statements rather than a loop: this runs inside a request, and
     * an account that has been signing in for a year has a lot of rows. The
     * subquery keeps the token ids in the database.
     */
    public function revokeOAuthTokens(): void
    {
        \Laravel\Passport\Passport::refreshTokenModel()::query()
            ->whereIn('access_token_id', $this->tokens()->select('id'))
            ->where('revoked', false)
            ->update(['revoked' => true]);

        $this->tokens()
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }

    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strip_tags($name);
    }

    /**
     * The invitations to this user's address, once it is verified: an
     * account can claim any address, and must not see or act on invitations
     * meant for someone else. Not for eager loading (`with('invitations')`):
     * that builds the relation on a blank user, which has no verified address.
     */
    public function invitations()
    {
        $invitations = $this->hasMany(Jetstream::teamInvitationModel(), 'email', 'email');

        return $this->hasVerifiedEmail() ? $invitations : $invitations->whereRaw('1 = 0');
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
        /**
         * @var \App\Models\Team $team
         */
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

    /**
     * Only superadmins may sign in as another user: the permission that
     * opens the admin pages (config admin.permission), with two-factor
     * sign-in confirmed. The package's default lets every user sign in as
     * anyone. The impersonation routes also ask for a code if the session
     * has not seen one (EnsureSuperadminTwoFactor).
     */
    public function canImpersonate()
    {
        return $this->can(config('admin.permission'))
            && $this->hasEnabledTwoFactorAuthentication();
    }

    /**
     * Nobody may sign in as a superadmin, not even another superadmin.
     */
    public function canBeImpersonated()
    {
        return !$this->can(config('admin.permission'));
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

        return $team !== null && $this->licenseTeam->id === $team->id;
    }

    public function applyAcceptedInvitiations()
    {
        // The invitation went to an address; only its verified owner takes it
        // up, not whoever registers with that address first.
        if (! $this->hasVerifiedEmail()) {
            return;
        }

        # are there any remaining invitations that were previously accept?
        $invitations = TeamInvitation::where('email', $this->email)
            ->where('accepted', true)
            ->orderBy('updated_at', 'asc')
            ->get();

        if ($invitations->count() === 0) {
            return;
        }

        foreach ($invitations as $invitation) {
            // An invitation to own the team, handed over rather than joined.
            // Before the membership check: the invitee may already be in it.
            if ($invitation->as_owner) {
                try {
                    app(HandOverInvitedTeam::class)->hand($this, $invitation);
                } catch (AuthorizationException $e) {
                    // No longer valid; dropped rather than retried on every request.
                    Log::warning('An owner invitation could not be handed over', ['invitation' => $invitation->id, 'reason' => $e->getMessage()]);
                    $invitation->delete();
                }
                $this->unsetRelation('ownedTeams');
                $this->unsetRelation('teams');
                $this->unsetRelation('currentTeam');

                continue;
            }

            // Already on the team, for example through an earlier invitation:
            // adding again would fail on every request.
            if ($invitation->team->hasUser($this)) {
                $invitation->delete();

                continue;
            }

            app(AddsTeamMembers::class)->add(
                $invitation->team->owner,
                $invitation->team,
                $invitation->email,
                $invitation->role
            );

            $invitation->delete();

            // switchTeam() checks membership against the loaded relation,
            // which does not know the team this user was just added to.
            $this->unsetRelation('teams');
            $this->switchTeam($invitation->team);

            // update notification count
            dispatch(new SyncUserToNlpApi($invitation->team->owner, 'high'));
        }
    }

    public function getNotificationCount()
    {
        return $this->invitations->count();
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

    protected function getUserClients()
    {
        $clients = [];
        /**
         * @var \App\Models\ConnectedAccount $connectedAccount
         */
        foreach ($this->connectedAccounts as $connectedAccount) {
            if ($connectedAccount->provider == OAuthController::OFFICE_PROVIDER) {
                $clients[] = 'word';
            }
        }

        return array_unique($clients);
    }

    public function getUserData($booleanAsStrings = false)
    {
        $true = $booleanAsStrings ? 'Yes' : true;
        $false = $booleanAsStrings ? 'No' : false;

        $data = [
            'witty_account_created_at' => $this->created_at->__toString(),
            'user_role' => $this->role,
            'how_did_you_find' => $this->found,
            'has_witty_account' => $true,
            'has_consented_to_mailing' => $this->has_consented_to_mailing ? $true : $false,
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
