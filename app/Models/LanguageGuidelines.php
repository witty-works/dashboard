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

        return self::where('team_id', $team->id)->firstOrNew();
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
}
