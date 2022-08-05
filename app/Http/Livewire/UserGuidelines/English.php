<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class English extends Component
{
    use AuthorizesRequests, AttributeTrait;

    public $singular_they;

    protected $rules = [
        'singular_they' => 'nullable|boolean',
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

        $this->singular_they = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'singular_they',
            'english_rules'
        );
    }

    public function updateLanguageGuidelinesEnglish()
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $languageGuidelines->singular_they = (bool) $this->singular_they;

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
        return view('livewire.user-guidelines.english');
    }

    protected function getLanguageGuidelines($user)
    {
        return LanguageGuidelines::firstOrNew(['user_id' => $user->id]);
    }
}
