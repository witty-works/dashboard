<?php

namespace App\Http\Livewire\UserCategorySettings;

use App\Http\Livewire\OrganizationCategorySettings\Category as OrganizationCategorySettingsCategory;
use App\Http\Livewire\UserGuidelineTrait;
use App\Jobs\SyncUserToNlpApi;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Category extends OrganizationCategorySettingsCategory
{
    use AuthorizesRequests;
    use UserGuidelineTrait {
        mount as traitMount;
    }

    protected $rules = [
        'dimensions.*' => 'nullable|int|max:2',
    ];

    public function mount($model, $category = null, $config = null)
    {
        parent::mount($model, $category, $config);
    }

    public function resetForm()
    {
        parent::resetForm();
    }

    protected function readDimensions($disabledCategories)
    {
        if (LanguageGuidelines::isForcedOnTeam($this->model, 'disabled_categories', $this->category)) {
            $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model->currentTeam);
            $disabledCategories = (array) $languageGuidelines->disabled_categories;
        }

        return parent::readDimensions($disabledCategories);
    }

    public function updateLanguageGuidelinesCategory()
    {
        if (LanguageGuidelines::isForcedOnTeam($this->model, 'disabled_categories', $this->category)) {
            $this->mount($this->model);

            return;
        }

        foreach ($this->dimensions as $ddd => $enabled) {
            $this->dimensions[$ddd] = (int)$enabled;
        }

        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
        $languageGuidelines->save();

        $this->dimensions = $this->processDimensions($languageGuidelines);

        $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());

        $this->emit('saved');
        $this->updateHelpHero();

        dispatch(new SyncUserToNlpApi($this->model, 'high'));
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.user-category-settings.category');
    }
}
