<?php

namespace App\Models;

use App\Helpers\CategoryDataHelper;
use App\Helpers\PosthogHelper;
use App\Http\Controllers\Livewire\UserGuidelinesController;
use App\Jobs\SendEventToPosthog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LanguageGuidelines extends Model
{
    const DISABLED = 0;
    const BASIC_ENABLED = 1;
    const ADVANCED_ENABLED = 2;

    static public $tripleToogleLoaded = false;

    use HasFactory;
    use GuidelinesUpdateTrait {
        fireCustomModelEvent as fireCustomModelEventParent;
    }

    protected $attributes = [
        'german_gender_ending' => '*in',
        'french_gender_separator' => '·',
        'gendered_roles_format' => 'inclusive_gender',
        'show_inspiration_alternatives' => false,
        'generic_masculine_force' => true,
        'german_rules_force' => true,
        'french_rules_force' => true,
        'show_inspiration_alternatives_force' => true,
        'preferred_variants_force' => true,
    ];

    protected $fillable = [
        'user_id',
        'team_id',
        'preferred_variants',
        'disabled_categories',
    ];

    protected $casts = [
        'preferred_variants' => 'json',
        'disabled_categories' => 'json',
        'disabled_categories_force' => 'json',
        'generic_masculine_force' => 'boolean',
        'german_rules_force' => 'boolean',
        'french_rules_force' => 'boolean',
        'show_inspiration_alternatives_force' => 'boolean',
        'preferred_variants_force' => 'boolean',
    ];

    protected static $syncFields = [
        'preferred_languages',
        'preferred_variants',
        'german_gender_ending',
        'french_gender_separator',
        'gendered_roles_format',
        'show_inspiration_alternatives',
    ];

    public $proficiencyLevels;

    public $diversityDimensionDrivers;

    public function __construct(array $attributes = [])
    {
        $this->proficiencyLevels = CategoryDataHelper::loadTableData('proficiency_levels');
        $this->diversityDimensionDrivers = CategoryDataHelper::loadTableData('diversity_dimension_drivers');

        $disabled_categories = [];
        foreach ($this->getDiversityDimensionDrivers(null, true, true) as $ddd => $config) {
            $disabled_categories[] = $ddd;
        }

        $attributes += [
            'preferred_variants' => ['de-DE', 'en-US'],
            'disabled_categories' => $disabled_categories,
            'disabled_categories_force' => [],
        ];

        parent::__construct($attributes);
    }

    public function getModelAttribute()
    {
        if ($this->team_id) {
            return $this->team;
        }

        if ($this->user_id) {
            return $this->user;
        }

        return null;
    }

    public static function getLanguageGuidelines($model)
    {
        $filter = [$model instanceof Team ? 'team_id' : 'user_id'  => $model->id];
        $languageGuideline = LanguageGuidelines::firstOrNew($filter);

        if ($languageGuideline->disabled_categories_force === null) {
            $languageGuideline->disabled_categories_force = [];
        }

        return $languageGuideline;
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isBasicOnly($proficiencyLevel)
    {
        return in_array($proficiencyLevel, ['openly_discriminating', 'inclusive']);
    }

    public static function getTeamGuidelines(User $user)
    {
        $team = $user->currentTeam;

        if (!$team) {
            return null;
        }

        return self::getLanguageGuidelines($team);
    }

    public function getGenderedRolesFormat($genderedRolesFormat = null)
    {
        if ($genderedRolesFormat === null) {
            $genderedRolesFormat = $this->gendered_roles_format;
        }

        if (!array_key_exists($genderedRolesFormat, GuidelinesInterface::GENDERED_ROLES_FORMAT)) {
            return key(GuidelinesInterface::GENDERED_ROLES_FORMAT);
        }

        return $genderedRolesFormat;
    }

    public function getDiversityDimensionDrivers(
        $category = null,
        $includeAdvanced = false,
        $advancedOnly = false
    ) {

        if ($category === null && $includeAdvanced === false) {
            return $this->diversityDimensionDrivers;
        }

        $diversityDimensionDrivers = [];
        foreach ($this->diversityDimensionDrivers as $ddd => $dddConfig) {
            if (empty($dddConfig['category']) || $dddConfig['category'] === 'orthography') {
                continue;
            }

            if ($category && ($category !== $dddConfig['category'] || empty($dddConfig['translation']))) {
                continue;
            }

            if (!$advancedOnly) {
                $diversityDimensionDrivers[$ddd] = $dddConfig;
            }

            if (
                $includeAdvanced
                && !empty($dddConfig['proficiency_level'])
                && !self::isBasicOnly($dddConfig['proficiency_level'])
            ) {
                $diversityDimensionDrivers['advanced_' . $ddd] = $dddConfig;
            }
        }

        return $diversityDimensionDrivers;
    }

    public function adjustLevel($ddd, $level)
    {
        $diversityDimensionDrivers = $this->getDiversityDimensionDrivers($this->category);
        $proficiencyLevel = $diversityDimensionDrivers[$ddd]['proficiency_level'] ?? null;

        if ($proficiencyLevel === 'openly_discriminating') {
            $level = LanguageGuidelines::BASIC_ENABLED;
        } elseif ($proficiencyLevel === 'inclusive' && $level === LanguageGuidelines::ADVANCED_ENABLED) {
            $level = LanguageGuidelines::BASIC_ENABLED;
        } elseif (empty($level)) {
            $level = LanguageGuidelines::DISABLED;
        }

        switch ($level) {
            case LanguageGuidelines::ADVANCED_ENABLED:
                $this->inPlaceUpateArray('advanced_' . $ddd, 'disabled_categories', true);
                $this->inPlaceUpateArray($ddd, 'disabled_categories', true);
                break;
            case LanguageGuidelines::BASIC_ENABLED:
                if (!LanguageGuidelines::isBasicOnly($proficiencyLevel)) {
                    $this->inPlaceUpateArray('advanced_' . $ddd, 'disabled_categories', false);
                }
                $this->inPlaceUpateArray($ddd, 'disabled_categories', true);
                break;
            case LanguageGuidelines::DISABLED:
            default:
                if (!LanguageGuidelines::isBasicOnly($proficiencyLevel)) {
                    $this->inPlaceUpateArray('advanced_' . $ddd, 'disabled_categories', false);
                }
                $this->inPlaceUpateArray($ddd, 'disabled_categories', false);
                break;
        }
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

        $this->refresh();
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

        switch ($type) {
            case 'Language':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'preferred_variants' => $this->preferred_variants,
                    'force' => $this->preferred_variants_force,
                ];
                break;
            case 'German':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'german_gender_ending' => $this->german_gender_ending,
                    'force' => $this->german_rules_force,
                ];
                break;
            case 'French':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'french_gender_separator' => $this->french_gender_separator,
                    'force' => $this->german_rules_force,
                ];
                break;
            case 'GenericMasculine':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'gendered_roles_format' => $this->gendered_roles_format,
                    'force' => $this->generic_masculine_force,
                ];
                break;
            case 'Inspiration':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'show_inspiration_alternatives' => $this->show_inspiration_alternatives,
                    'force' => $this->show_inspiration_alternatives_force,
                ];
                break;
            case 'Category':
                $disabled_categories_force = (array) $this->disabled_categories_force;
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

    public static function isForcedOnTeam(User $user, $section, $category = null)
    {

        $teamGuidelines = self::getTeamGuidelines($user);

        if (!$teamGuidelines) {
            return false;
        }

        if ($category) {
            return in_array($category, $teamGuidelines->disabled_categories_force) ? 'locked' : false;
        }

        return $teamGuidelines->{$section . '_force'} ? 'locked' : false;
    }

    public static function teamCategoryValue(User $user, $category, $proficiencyLevel)
    {
        $teamGuidelines = self::getTeamGuidelines($user);

        if (!$teamGuidelines) {
            return false;
        }

        if (
            in_array('advanced_' . $category, $teamGuidelines->disabled_categories)
            || self::isBasicOnly($proficiencyLevel)
        ) {
            if (in_array($category, $teamGuidelines->disabled_categories)) {
                return self::DISABLED;
            }

            return self::BASIC_ENABLED;
        }

        return self::ADVANCED_ENABLED;
    }

    public static function doUserTeamSettingsDiffer($user, $type)
    {
        if (!$user->currentTeam) {
            return false;
        }

        $teamLanguageGuidelines = LanguageGuidelines::where('team_id', $user->currentTeam->id)->first();
        if (!$teamLanguageGuidelines) {
            return false;
        }

        $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

        switch ($type) {
            case UserGuidelinesController::CATEGORY_SETTINGS:
                $diversityDimensionDrivers = CategoryDataHelper::loadTableData('diversity_dimension_drivers');

                foreach ($diversityDimensionDrivers as $ddd => $config) {
                    if (!empty($config['category']) && $config['category'] === 'orthography') {
                        continue;
                    }
                    if (in_array($ddd, $languageGuidelines->disabled_categories) !== in_array($ddd, $teamLanguageGuidelines->disabled_categories)) {
                        return true;
                    }
                    if (in_array('advanced_' . $ddd, $languageGuidelines->disabled_categories) !== in_array('advanced_' . $ddd, $teamLanguageGuidelines->disabled_categories)) {
                        return true;
                    }
                }
                break;
            case UserGuidelinesController::LANGUAGE_SETTINGS:
                foreach (self::$syncFields as $field) {
                    if ($languageGuidelines->{$field} !== $teamLanguageGuidelines->{$field}) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }

    public function resetSettingsToTeam($teamLanguageGuidelines, $type)
    {
        switch ($type) {
            case UserGuidelinesController::CATEGORY_SETTINGS:
                $this->disabled_categories = $teamLanguageGuidelines->disabled_categories;

                break;
            case UserGuidelinesController::LANGUAGE_SETTINGS:
                foreach (self::$syncFields as $field) {
                    $this->{$field} = $teamLanguageGuidelines->{$field};
                }

                break;
        }

        $this->save();
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
