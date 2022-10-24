<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LanguageGuidelines extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait;

    protected $attributes = [
        'german_gender_ending' => '*in',
        'gendered_roles_format' => 'inclusive_gender',
        'singular_they' => false,
        'expert_mode' => false,
        'show_inspiration_alternatives' => false,
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'preferred_variants',
        'disabled_categories',
        'disabled_categories_force',
    ];

    protected $casts = [
        'preferred_variants' => 'json',
        'disabled_categories' => 'json',
        'disabled_categories_force' => 'json',
        'expert_mode_force' => 'boolean',
        'english_rules_force' => 'boolean',
        'german_rules_force' => 'boolean',
        'show_inspiration_alternatives_force' => 'boolean',
        'preferred_variants_force' => 'boolean',
    ];

    static protected $syncFields = [
        'preferred_languages',
        'preferred_variants',
        'german_gender_ending',
        'gendered_roles_format',
        'singular_they',
        'show_inspiration_alternatives',
    ];

    public function __construct(array $attributes = [])
    {
        $attributes += [
            'preferred_variants' => ['de-DE', 'en-US'],
            'disabled_categories' => [],
            'disabled_categories_force' => [],
        ];

        parent::__construct($attributes);
    }

    public function inPlaceUpateArray($element, $column, $enabled)
    {
        $params = [':id' => $this->id, ':element' => $element, ':element_json' => "\"$element\""];

        if ($enabled) {
            $query = "
                UPDATE language_guidelines
                    SET {$column} = JSON_REMOVE({$column}, JSON_UNQUOTE(JSON_SEARCH({$column}, 'one', :element)))
                WHERE id = :id AND JSON_CONTAINS({$column}, :element_json, '$')
            ";
        } else {
            $query = "
                UPDATE language_guidelines
                    SET {$column} = IF({$column} = '{}', :element_json, JSON_ARRAY_APPEND({$column}, '$', :element))
                WHERE id = :id AND NOT JSON_CONTAINS({$column}, :element_json_2, '$')
            ";

            $params[':element_json_2'] = $params[':element_json'];
        }

        DB::statement($query, $params);
    }

    static public function getTeamGuidelines(User $user)
    {
        $team = $user->currentTeam;

        if (!$team) {
            return null;
        }

        return self::firstOrNew(['team_id' => $team->id]);
    }

    static public function isForcedOnTeam(User $user, $section)
    {
        $teamGuidelines = self::getTeamGuidelines($user);

        if (!$teamGuidelines) {
            return false;
        }

        if (in_array($section, GuidelinesInterface::DISABLED_CATEGORIES)) {
            return in_array($section, $teamGuidelines->disabled_categories_force) ? 'locked' : false;
        }

        return $teamGuidelines->{$section . '_force'} ? 'locked' : false;
    }

    static public function doUserGuidelinesTeamDiffer($user)
    {
        if (!$user->currentTeam) {
            return false;
        }

        $teamLanguageGuidelines = LanguageGuidelines::where('team_id', $user->currentTeam->id)->first();
        if (!$teamLanguageGuidelines) {
            return false;
        }

        $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

        foreach (self::$syncFields as $field) {
            if ($languageGuidelines->{$field} !== $teamLanguageGuidelines->{$field}) {
                return true;
            }
        }

        return false;
    }

    static public function resetGuidelinesToTeam($user)
    {
        if (!$user->currentTeam) {
            return;
        }

        $teamLanguageGuidelines = LanguageGuidelines::where('team_id', $user->currentTeam->id)->first();
        if (!$teamLanguageGuidelines) {
            return;
        }

        $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

        foreach (self::$syncFields as $field) {
            $languageGuidelines->{$field} = $teamLanguageGuidelines->{$field};
        }

        $languageGuidelines->save();
    }
}
