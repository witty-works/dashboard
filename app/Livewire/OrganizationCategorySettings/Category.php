<?php

namespace App\Livewire\OrganizationCategorySettings;

use App\Livewire\TeamsGuidelineTrait;
use App\Jobs\SyncOrganizationToNlpApi;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Category extends Component
{
    use AuthorizesRequests;
    use TeamsGuidelineTrait {
        mount as traitMount;
    }

    protected $listeners = ['saved'];

    public $dimensions;

    protected $rules = [
        'dimensions.*' => 'nullable|int|max:2',
    ];

    public $model;

    public $category;

    public $config;

    public $proficiencyLevels;

    public $diversityDimensionDrivers;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($model, $category = null, $config = null)
    {
        if ($category !== null) {
            $this->category = $category;
        }
        if ($config !== null) {
            $this->config = $config;
        }

        $this->traitMount($model);
    }

    protected function resetForm()
    {
        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
        $this->proficiencyLevels = $languageGuidelines->proficiencyLevels;
        $this->diversityDimensionDrivers = $languageGuidelines->getDiversityDimensionDrivers($this->category);

        $disabledCategories = (array) $languageGuidelines->disabled_categories;
        $this->readDimensions($disabledCategories);

        $this->resetErrorBag();
    }

    protected function readDimensions($disabledCategories)
    {
        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
        $languages = [];
        foreach ($languageGuidelines->preferred_variants as $variant) {
            $languages[] = substr($variant, 0, 2);
        }

        foreach ($this->diversityDimensionDrivers as $ddd => $config) {
            if (empty($config['has_rules'])) {
                continue;
            }

            if (!in_array('advanced_' . $ddd, $disabledCategories)) {
                $this->dimensions[$ddd] = LanguageGuidelines::ADVANCED_ENABLED;
            } elseif (!in_array($ddd, $disabledCategories)) {
                $this->dimensions[$ddd] = LanguageGuidelines::BASIC_ENABLED;
            } else {
                $this->dimensions[$ddd] = LanguageGuidelines::DISABLED;
            }
        }
    }

    protected function processDimensions(LanguageGuidelines $languageGuidelines)
    {
        $dimensions = [];

        foreach ($this->dimensions as $ddd => $level) {
            $languageGuidelines->adjustLevel($ddd, $level);
        }

        return $dimensions;
    }

    public function updateLanguageGuidelinesCategory()
    {
        foreach ($this->dimensions as $ddd => $enabled) {
            $this->dimensions[$ddd] = (int)$enabled;
        }

        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
        $languageGuidelines->save();

        $this->dimensions = $this->processDimensions($languageGuidelines);

        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

        $this->dispatch('saved');
        $this->updateHelpHero();

        dispatch(new SyncOrganizationToNlpApi($this->model, 'high'));
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-category-settings.category');
    }
}
