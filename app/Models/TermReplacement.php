<?php

namespace App\Models;

use App\Helpers\PosthogHelper;
use App\Jobs\SendEventToPosthog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use function Emoji\detect_emoji;

class TermReplacement extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    const LANGUAGE_CODES = ['' => 'content.any', 'en' => 'content.en', 'de' => 'content.de'];
    const MATCHING_TYPES = [
        'case_insensitive' => 'guidelines.case_insensitive_long',
        'case_sensitive' => 'guidelines.case_sensitive_long',
        'lemmatize' => 'guidelines.lemmatize_long'
    ];
    const WORD_TYPES = [
        'a' => 'guidelines.adjective_long',
        'v' => 'guidelines.verb_long',
        'n' => 'guidelines.noun_long'
    ];

    public function getExistsOnTeamAttribute()
    {
        $team = $this->user->currentTeam;

        if (!$team) {
            return false;
        }

        $query = self::where('team_id', $team->id)
            ->where('term', $this->term);

        if ($this->language_code) {
            $query->where(function ($q) {
                $q->where('language_code', '')
                    ->orWhere('language_code', $this->language_code);
            });
        }

        return $query->exists();
    }

    public function getMatchingTypeAttribute()
    {
        switch ($this->word_type) {
            case '~':
                return 'case_insensitive';
            case '=':
                return 'case_sensitive';
            default:
                return 'lemmatize';
        }
    }

    public function dispatchEventToPosthog()
    {
        if ($this->user) {
            $model = $user = $this->user;
            $team = null;
            $event = PosthogHelper::STORE_TERM_REPLACEMENT;
        } else {
            $user = Auth::user();
            $model = $team = $this->team;
            $event = PosthogHelper::STORE_TEAM_TERM_REPLACEMENT;
        }

        $job = new SendEventToPosthog(
            $user,
            $event,
            [
                'total' => $model->termReplacements->count(),
                'replacement' => $this->replacement,
                'language_code' => $this->language_code,
                'word_type' => $this->word_type,

            ],
            !$this->wasRecentlyCreated,
            $team,
        );

        dispatch($job);
    }

    static public function validateEmoji($emoji)
    {
        $emoji = trim($emoji);
        if (empty($emoji)) {
            return $emoji;
        }

        $parsed_emoji = detect_emoji($emoji);

        if (count($parsed_emoji) != 1) {
            $message = __('guidelines.emoji_invalid_format');
            throw ValidationException::withMessages(['emoji' => $message]);
        }

        return $parsed_emoji[0]['emoji'];
    }
}
