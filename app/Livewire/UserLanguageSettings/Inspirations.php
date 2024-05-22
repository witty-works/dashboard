<?php

namespace App\Livewire\UserLanguageSettings;

use App\Livewire\UserGuidelineTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Inspirations extends Component
{
    use AuthorizesRequests;
    use UserGuidelineTrait;

    public $show_inspiration_alternatives;
    public $model;

    protected $rules = [
        'show_inspiration_alternatives' => 'nullable|boolean',
    ];

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);

        $this->show_inspiration_alternatives = (bool) $this->mountAttribute(
            $this->model,
            $languageGuidelines,
            'show_inspiration_alternatives'
        );
    }

    public function updateLanguageGuidelinesInspirations()
    {
        if (LanguageGuidelines::isForcedOnTeam($this->model, 'show_inspiration_alternatives')) {
            $this->mount($this->model);

            return;
        }

        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        if ($this->model->isPremium()) {
            $languageGuidelines = LanguageGuidelines::getLanguageGuidelines($this->model);
            $languageGuidelines->show_inspiration_alternatives = (bool) $this->show_inspiration_alternatives;

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
        return view('livewire.user-language-settings.inspirations');
    }
}
