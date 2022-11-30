<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Http\Livewire\HelpHeroTrait;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Inspirations extends Component
{
    use AuthorizesRequests, AttributeTrait;
    use HelpHeroTrait;

    public $show_inspiration_alternatives;

    protected $rules = [
        'show_inspiration_alternatives' => 'nullable|boolean',
    ];

    public $user;

    /**
     * Mount the component.
     *
     * @param  mixed  $user
     * @return void
     */
    public function mount($user)
    {
        $this->user = Auth::user();
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->show_inspiration_alternatives = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'show_inspiration_alternatives'
        );
    }

    public function updateLanguageGuidelinesInspirations()
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $languageGuidelines->show_inspiration_alternatives = (bool) $this->show_inspiration_alternatives;

        $languageGuidelines->save();

        $this->emit('saved');
        $this->updateHelpHero();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.user-guidelines.inspirations');
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    protected function getLanguageGuidelines($user)
    {
        $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

        if (!$this->user->subscribed()) {
            $this->show_inspiration_alternatives = false;
            $languageGuidelines->show_inspiration_alternatives = false;
        }

        return $languageGuidelines;
    }
}
