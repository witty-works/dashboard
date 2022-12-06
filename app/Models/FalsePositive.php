<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FalsePositive extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    const LANGUAGE_CODES = ['' => 'content.any', 'en' => 'content.en', 'de' => 'content.de'];

    public function getExistsOnTeamAttribute()
    {
        $team = $this->user->currentTeam;

        if (!$team) {
            return false;
        }

        return self::where('team_id', $team->id)->where('false_positive', $this->false_positive)->exists();
    }
}
