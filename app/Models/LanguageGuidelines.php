<?php

namespace App\Models;

use App\Helpers\PosthogHelper;
use App\Jobs\SendEventToPosthog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LanguageGuidelines extends Model
{
    use HasFactory;
    use GuidelinesUpdateTrait {
        fireCustomModelEvent as fireCustomModelEventParent;
    }

    protected $attributes = [
        'german_gender_ending' => '*in',
        'gendered_roles_format' => 'both',
        'singular_they' => false,
        'expert_mode' => false,
        'show_inspiration_alternatives' => false,
        'expert_mode_force' => true,
        'english_rules_force' => true,
        'german_rules_force' => true,
        'show_inspiration_alternatives_force' => true,
        'preferred_variants_force' => true,

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

    protected static $syncFields = [
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
            'disabled_categories_force' => [
                'inclusive' => true,
                'style' => true,
                'orthography' => true,
            ],
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

    public function dispatchEventToPosthog($type)
    {
        if ($this->user) {
            $user = $this->user;
            $team = null;
            $event = PosthogHelper::STORE_LANGUAGE;
        } else {
            $user = Auth::user();
            $team = $this->team;
            $event = PosthogHelper::STORE_TEAM_LANGUAGE;
        }

        $subscribed = $user->subscribed();

        switch ($type) {
            case 'English':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'singular_they' => $this->singular_they,
                    'force' => !$subscribed || $this->english_rules_force,
                ];
                break;
            case 'Language':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'preferred_variants' => $this->preferred_variants,
                    'force' => !$subscribed || $this->preferred_variants_force,
                ];
                break;
            case 'ExpertMode':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'expert_mode' => $this->expert_mode,
                    'force' => !$subscribed || $this->expert_mode_force,
                ];
                break;
            case 'German':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'german_gender_ending' => $this->german_gender_ending,
                    'gendered_roles_format' => $this->gendered_roles_format,
                    'force' => !$subscribed || $this->german_rules_force,
                ];
                break;
            case 'Inspiration':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'show_inspiration_alternatives' => $this->show_inspiration_alternatives,
                    'force' => !$subscribed || $this->show_inspiration_alternatives_force,
                ];
                break;
            case 'Inclusive':
            case 'Orthography':
            case 'Style':
                if (!$subscribed) {
                    $disabled_categories_force = [];
                    foreach ($this->disabled_categories_force as $key => $value) {
                        $disabled_categories_force[$key] = true;
                    }
                } else {
                    $disabled_categories_force = $this->disabled_categories_force;
                }
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'disabled_categories' => $this->disabled_categories,
                    'force' => $disabled_categories_force,
                ];
                break;
            default:
                $properties = [];
        }

        if (!empty($properties)) {
            $job = new SendEventToPosthog(
                $user,
                $event,
                $properties,
                !$this->wasRecentlyCreated,
                $team,
            );

            dispatch($job);

            return true;
        }

        return false;
    }

    public static function getTeamGuidelines(User $user)
    {
        $team = $user->currentTeam;

        if (!$team) {
            return null;
        }

        return self::firstOrNew(['team_id' => $team->id]);
    }

    public static function isForcedOnTeam(User $user, $section)
    {
        if (!$user->subscribed()) {
            return 'locked_upgrade';
        }

        $teamGuidelines = self::getTeamGuidelines($user);

        if (!$teamGuidelines) {
            return false;
        }

        if (in_array($section, GuidelinesInterface::DISABLED_CATEGORIES)) {
            return in_array($section, $teamGuidelines->disabled_categories_force) ? 'locked' : false;
        }

        return $teamGuidelines->{$section . '_force'} ? 'locked' : false;
    }

    public static function doUserGuidelinesTeamDiffer($user)
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

    public static function resetGuidelinesToTeam($user)
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

    protected function fireCustomModelEvent($event, $method)
    {
        $this->fireCustomModelEventParent($event, $method);

        if ($event !== 'updating') {
            return;
        }

        if (!$this->isDirty('customized')) {
            $this->customized = true;
        }
    }
}
