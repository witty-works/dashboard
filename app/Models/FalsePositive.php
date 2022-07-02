<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FalsePositive extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    const LANGUAGE_CODE = ['' => 'content.any', 'de' => 'content.de', 'en' => 'content.en'];

    public function getExistsOnTeamAttribute()
    {
        $team = $this->user->currentTeam;

        if (!$team) {
            return false;
        }

        return self::where('team_id', $team->id)->where('false_positive', $this->false_positive)->exists();
    }
}
