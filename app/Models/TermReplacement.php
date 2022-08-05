<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use function Emoji\is_single_emoji;

class TermReplacement extends Model
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

        return self::where('team_id', $team->id)->where('term', $this->term)->exists();
    }

    static public function validateEmoji($emoji)
    {
        if ($emoji !== "" && $emoji !== null && !is_single_emoji($emoji)) {
            $message = __('guidelines.emoji_invalid_format');
            throw ValidationException::withMessages(['emoji' => $message]);
        }
    }
}
