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
    public $dimensions_force;

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

        $disabledCategoriesForce = (array) $languageGuidelines->disabled_categories_force;
        if (!$this->model->subscribed()) {
            $this->dimensions_force = true;
        } else {
            $this->dimensions_force = in_array($this->category, $disabledCategoriesForce);
        }

        $this->resetErrorBag();
    }

    protected function readDimensions($disabledCategories)
    {
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

    protected function processDimensions(LanguageGuidelines $languageGuidelines, $dimensions)
    {
        foreach ($this->dimensions as $ddd => $level) {
            $languageGuidelines->adjustLevel($ddd, $level);
        }
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

        $this->processDimensions($languageGuidelines, $this->dimensions);

        if (!$this->model->subscribed()) {
            $this->dimensions_force = true;
        }
        $languageGuidelines->inPlaceUpateArray($this->category, 'disabled_categories_force', !$this->dimensions_force);

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
