<?php

namespace App\Livewire\UserLanguageSettings;

use App\Livewire\UserGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Orthography extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    public $orthography;
    public $model;

    protected $rules = [
        'orthography' => 'nullable|boolean',
    ];

    protected function resetForm()
    {
        if (LanguageGuidelines::isForcedOnTeam($this->model, 'disabled_categories', 'orthography')) {
            $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model->currentTeam);
        } else {
            $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
        }

        $this->resetErrorBag();

        $this->orthography = !in_array('orthography', $languageGuidelines->disabled_categories);
    }

    public function updateLanguageGuidelinesOrthography()
    {
        if (LanguageGuidelines::isForcedOnTeam($this->model, 'orthography')) {
            $this->mount($this->model);

            return;
        }

        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        if ($this->model->isPremium()) {
            $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
            $languageGuidelines->inPlaceUpateArray('orthography', 'disabled_categories', $this->orthography);

            $languageGuidelines->save();
            $languageGuidelines->dispatchEventToPosthog((new \ReflectionClass($this))->getShortName());
        }

        $this->dispatch('saved');
        $this->updateHelpHero();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.user-language-settings.orthography');
    }
}
