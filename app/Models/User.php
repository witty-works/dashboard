<?php

namespace App\Models;

use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Providers\AppServiceProvider;
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
        return AppServiceProvider::POSTHOG_ID_PREFIX . $this->id;
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

    public function subscribed($name = 'witty', $price = null)
    {
        $team = $this->currentTeam;
        if ($team) {
            return $team->subscribed($name, $price);
        }
    }

    public function subscription($name = 'witty')
    {
        $team = $this->currentTeam;
        if ($team) {
            return $team->subscription($name);
        }
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
    }

    public function getNotificationCount()
    {
        return $this->invitations->count();
    }

    public function getHubspotData()
    {
        return [
            'has_witty_account' => 'Yes',
            'has_consented_to_mailing' => $this->has_consented_to_mailing ? 'Yes' : 'No',
            'has_accessed_stripe' => $this->has_accessed_stripe ? 'Yes' : 'No',
        ];
    }

    public function syncHubspot()
    {
        if (!$this->hubspot_id) {
            return false;
        }

        $data = $this->getHubspotData();

        $hubspot = \HubSpot\Factory::createWithAccessToken(config('hubspot.access_token'));
        $newProperties = new \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput();
        $newProperties->setProperties($data);

        $hubspot->crm()->contacts()->basicApi()->update($this->hubspot_id, $newProperties);

        return true;
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
