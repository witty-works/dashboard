<?php

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $categories = [
        'style' => [
            "abbreviation",
            "advanced_abbreviation",
            "anglicism",
            "advanced_anglicism",
            "exaggerating",
            "advanced_exaggerating",
            "false_friends",
            "advanced_false_friends",
            "filler",
            "advanced_filler",
            "formality",
            "advanced_formality",
            "general_style",
            "advanced_general_style",
            "hollow",
            "advanced_hollow",
            "redundancy",
            "advanced_redundancy",
            "regionalisms",
            "advanced_regionalisms",
            "repetitions_style",
            "advanced_repetitions_style",
            "semantics",
            "advanced_semantics",
            "plain_language",
            "advanced_plain_language",
            "style",
            "advanced_style",
        ],
        'inclusive' => [
            "d_and_i",
            "advanced_d_and_i",
            "communal",
            "advanced_communal",
            "emotional_security",
            "advanced_emotional_security",
        ],
        'orthography' => [
            'orthography',
        ],
    ];

    protected $newCategories = ['orthography'];

    protected $diversityDimensionDrivers;

    protected $advanced = [];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->diversityDimensionDrivers = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers');

        foreach ($this->diversityDimensionDrivers as $ddd => $config) {
            if (empty($config['category']) || empty($config['proficiency_level']) || $config['proficiency_level'] === 'openly_discriminating') {
                continue;
            }

            $this->advanced[] = 'advanced_' . $ddd;
        }

        $categories = SyncToHubspotCategoriesCommand::loadTableData('categories');
        $this->newCategories += $categories->keys()->toArray();

        $query = Team::query();
        foreach ($query->cursor() as $team) {
            $this->updateGuidelines($team);
        }

        $query = User::query();
        foreach ($query->cursor() as $user) {
            $this->updateGuidelines($user);
        }
    }

    protected function updateGuidelines($model)
    {
        $guidelines = $model->languageGuidelines;
        if (!$guidelines) {
            return;
        }

        $disabledCategoriesOriginal = $guidelines->disabled_categories;
        $disabledCategories = [];

        // expert_mode
        if (!$model->subscribed() || !$guidelines->expert_mode) {
            # disable all `advanced_` categories
            $disabledCategories = $this->advanced;
        }

        # handle top level categories, ie. style+inclusive+orthography
        foreach ($this->categories as $category => $ddds) {
            if (in_array($category, $disabledCategoriesOriginal)) {
                $disabledCategories += $ddds;
            }
        }

        // gendered_roles_format / singular_they
        if ($model->subscribed()) {
            if (in_array($guidelines->gendered_roles_format, GuidelinesInterface::GENDERED_ROLES_FORMAT_ADVANCED)) {
                $key = array_search('advanced_gendered_denominations_ending', $disabledCategories);
                if ($key) {
                    array_splice($disabledCategories, $key, 1);
                }
            }

            if ($guidelines->singular_they) {
                $key = array_search('advanced_binary_pronouns', $disabledCategories);
                if ($key) {
                    array_splice($disabledCategories, $key, 1);
                }
            }
        } else {
            $guidelines->gendered_roles_format = 'binary_gender';
        }

        $guidelines->disabled_categories = array_unique($disabledCategories);

        // disabled_categories_force
        if ($model instanceof Team) {
            if ($model->subscribed()) {
                if (is_array($guidelines->disabled_categories_force) && in_array('orthography', $guidelines->disabled_categories_force)) {
                    $guidelines->disabled_categories_force = ['orthography'];
                } else {
                    $guidelines->disabled_categories_force = [];
                }
            } else {
                $guidelines->disabled_categories_force = $this->newCategories;
            }
        } else {
            $guidelines->disabled_categories_force = null;
        }

        $guidelines->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
