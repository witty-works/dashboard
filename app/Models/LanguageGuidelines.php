<?php

namespace App\Models;

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Helpers\PosthogHelper;
use App\Http\Controllers\Livewire\UserGuidelinesController;
use App\Jobs\SendEventToPosthog;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LanguageGuidelines extends Model
{
    const DISABLED = 0;
    const BASIC_ENABLED = 1;
    const ADVANCED_ENABLED = 2;

    use HasFactory;
    use GuidelinesUpdateTrait {
        fireCustomModelEvent as fireCustomModelEventParent;
    }

    protected $attributes = [
        'german_gender_ending' => '*in',
        'gendered_roles_format' => null,
        'show_inspiration_alternatives' => false,
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
        'german_rules_force' => 'boolean',
        'show_inspiration_alternatives_force' => 'boolean',
        'preferred_variants_force' => 'boolean',
    ];

    protected static $syncFields = [
        'preferred_languages',
        'preferred_variants',
        'german_gender_ending',
        'gendered_roles_format',
        'show_inspiration_alternatives',
    ];

    public $proficiencyLevels;

    public $diversityDimensionDrivers;

    public function __construct(array $attributes = [])
    {
        $attributes += [
            'preferred_variants' => ['de-DE', 'en-US'],
            'disabled_categories' => [],
            'disabled_categories_force' => [
                'orthography',
            ],
        ];

        parent::__construct($attributes);

        $this->proficiencyLevels = SyncToHubspotCategoriesCommand::loadTableData('proficiency_levels');
        $this->diversityDimensionDrivers = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers');
    }

    public static function getLanguageGuidelines($model)
    {
        if ($model instanceof Team) {
            $filter = ['team_id' => $model->id];
        } else {
            $filter = ['user_id' => $model->id];
        }

        $languageGuideline = LanguageGuidelines::firstOrNew($filter);
        $subscribed = $model->subscribed();

        $disabled_categories = $languageGuideline->disabled_categories;
        foreach ($languageGuideline->diversityDimensionDrivers as $ddd => $config) {
            if (!$languageGuideline->isCategoryAvailable($ddd, $subscribed) && !in_array($ddd, $languageGuideline->disabled_categories)) {
                $disabled_categories[] = $ddd;
            }
        }

        $languageGuideline->disabled_categories = $disabled_categories;

        return $languageGuideline;
    }

    public static function getTeamGuidelines(User $user)
    {
        $team = $user->currentTeam;

        if (!$team) {
            return null;
        }

        return self::getLanguageGuidelines($team);
    }

    public function genderedRolesFormats(): Attribute
    {
        $disabled_categories = $this->disabled_categories;

        return Attribute::make(
            get: function ($value) use ($disabled_categories) {
                if (in_array('gendered_denominations_ending', $disabled_categories)) {
                    return [];
                }

                return in_array('advanced_gendered_denominations_ending', $disabled_categories)
                    ? GuidelinesInterface::GENDERED_ROLES_FORMAT
                    : GuidelinesInterface::GENDERED_ROLES_FORMAT_ADVANCED;
            },
        );
    }

    protected function genderedRolesFormat(): Attribute
    {
        $genderedRolesFormats = $this->gendered_roles_formats;

        $func = function ($value) use ($genderedRolesFormats) {
            if (!array_key_exists($value, $genderedRolesFormats)) {
                return key($genderedRolesFormats);
            }

            return $value;
        };

        return Attribute::make(
            get: $func,
            set: $func,
        );
    }

    /**
     * 
     * @param mixed $diversityDimensionDriver 
     * @param mixed $subscribed 
     * @param str|null $enabled  DISABLED|BASIC_ENABLED|ADVANCED_ENABLED
     * @return str|null          DISABLED|BASIC_ENABLED|ADVANCED_ENABLED
     */
    public function isCategoryAvailable($diversityDimensionDriver, $subscribed, $enabled = self::ADVANCED_ENABLED)
    {
        $proficiencyLevel = $this->diversityDimensionDrivers[$diversityDimensionDriver]['proficiency_level'] ?? null;
        if ($proficiencyLevel === 'openly_discriminating') {
            return self::BASIC_ENABLED;
        }

        if (empty($enabled)) {
            return self::DISABLED;
        }

        if (!$subscribed) {
            return self::BASIC_ENABLED;
        }

        return $enabled;
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

        $subscribed = $user->subscribed();

        switch ($type) {
            case 'Language':
                $properties = [
                    'language_type' => (new \ReflectionClass($this))->getShortName(),
                    'preferred_variants' => $this->preferred_variants,
                    'force' => !$subscribed || $this->preferred_variants_force,
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
            case 'Orthography':
            case 'Category':
                if (!$subscribed) {
                    $disabled_categories_force = [];
                    foreach ($this->disabled_categories_force as $key => $value) {
                        $disabled_categories_force[] = $key;
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

    public static function isForcedOnTeam(User $user, $section, $category = null)
    {
        if (!$user->subscribed()) {
            return 'locked_upgrade';
        }

        $teamGuidelines = self::getTeamGuidelines($user);

        if (!$teamGuidelines) {
            return false;
        }

        if ($category) {
            return in_array($category, $teamGuidelines->disabled_categories_force) ? 'locked' : false;
        }

        if (in_array($section, GuidelinesInterface::DISABLED_CATEGORIES)) {
            return in_array($section, $teamGuidelines->disabled_categories_force) ? 'locked' : false;
        }

        return $teamGuidelines->{$section . '_force'} ? 'locked' : false;
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
                $diversityDimensionDrivers = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers');

                foreach ($diversityDimensionDrivers as $ddd => $config) {
                    if (in_array($ddd, $languageGuidelines->disabled_categories) !== in_array($ddd, $teamLanguageGuidelines->disabled_categories)) {
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
                $diversityDimensionDrivers = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers');

                foreach ($diversityDimensionDrivers as $ddd => $config) {
                    $disabledCategories = $teamLanguageGuidelines->disabled_categories;
                    if (in_array('orthography', $disabledCategories)) {
                        unset($disabledCategories[array_search('orthography', $disabledCategories)]);
                    }
                    $this->disabled_categories = $disabledCategories;
                }

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
