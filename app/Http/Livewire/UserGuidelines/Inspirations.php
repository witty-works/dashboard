<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Inspirations extends Component
{
    use AuthorizesRequests, AttributeTrait;

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
        $this->user = $user;

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
