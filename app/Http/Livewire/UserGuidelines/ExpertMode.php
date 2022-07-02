<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ExpertMode extends Component
{
    use AuthorizesRequests, AttributeTrait;

    public $expert_mode;

    protected $rules = [
        'expert_mode' => 'nullable|boolean',
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

        $this->expert_mode = (bool) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'expert_mode'
        );
    }

    public function updateLanguageGuidelinesExpertMode()
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $languageGuidelines->expert_mode = (bool) $this->expert_mode;

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
        return view('livewire.user-guidelines.expert-mode');
    }

    protected function getLanguageGuidelines($user)
    {
        $languageGuidelines = LanguageGuidelines::firstOrNew(['user_id' => $user->id]);

        if (!$this->user->subscribed()) {
            $this->expert_mode = false;
            $languageGuidelines->expert_mode = false;
        }

        return $languageGuidelines;
    }
}
