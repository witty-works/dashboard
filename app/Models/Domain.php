<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Domain extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    const TYPES = [
        'allow' => 'guidelines.allow',
        'allow_witty_works' => 'guidelines.allow_witty_works',
        'deny' => 'guidelines.deny'
    ];

    protected $fillable = [
        'domain',
    ];

    public function getExistsOnTeamAttribute()
    {
        $team = $this->user->currentTeam;

        if (!$team) {
            return false;
        }

        return self::where('team_id', $team->id)->where('domain', $this->domain)->exists();
    }


    static public function validateDomain($domain)
    {
        if (strpos($domain, '://') !== false) {
            $domain = parse_url($domain, PHP_URL_HOST);
        }

        if (preg_match("/[^\.\/]+\.[^\.\/]+$/", $domain, $matches)) {
            $domain = $matches[0];
        }

        if (
            !filter_var('https://' . $domain, FILTER_VALIDATE_URL)
            || strpos($domain, '.') === false
            || substr($domain, -1) === '.'
        ) {
            $message = __('guidelines.domain_not_valid');
            throw ValidationException::withMessages(['domain' => $message]);
        }

        return $domain;
    }
}
