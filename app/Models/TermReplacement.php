<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use function Emoji\detect_emoji;

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
        $parsed_emoji = detect_emoji($emoji);

        if (count($parsed_emoji) != 1) {
            $message = __('guidelines.emoji_invalid_format');
            throw ValidationException::withMessages(['emoji' => $message]);
        }

        return $parsed_emoji[0]['emoji'];
    }
}
