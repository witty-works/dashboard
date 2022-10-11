<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Language extends Component
{
    use AuthorizesRequests, AttributeTrait;

    public $preferred_variants;
    public $preferred_variants_de;
    public $preferred_variants_en;

    protected $rules = [
        'preferred_variants_de' => 'nullable|string|in:both,de-DE,de-AT,de-CH',
        'preferred_variants_en' => 'nullable|string|in:both,en-US,en-GB',
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

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->preferred_variants = (array) $this->mountAttribute(
            $this->user,
            $languageGuidelines,
            'preferred_variants'
        );

        foreach (GuidelinesInterface::LANGUAGES as $lang) {
            $property = "preferred_variants_$lang";
            foreach (constant('App\Models\GuidelinesInterface::PREFERRED_VARIANTS_' . strtoupper($lang)) as $locale => $trans_key) {
                if (in_array($locale, $this->preferred_variants)) {
                    $this->$property = $locale;
                }
            }
        }
    }

    public function updateLanguageGuidelinesLanguage()
    {
        $this->validate();

        $languageGuidelines = $this->getLanguageGuidelines($this->user);

        $this->preferred_variants = [];
        foreach (GuidelinesInterface::LANGUAGES as $lang) {
            $property = "preferred_variants_$lang";
            if ($this->$property) {
                $this->preferred_variants[] = $this->$property;
            }
        }

        $languageGuidelines->preferred_variants = $this->preferred_variants;

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
        return view('livewire.user-guidelines.language');
    }

    protected function getLanguageGuidelines($user)
    {
        return LanguageGuidelines::firstOrNew(['user_id' => $user->id]);
    }
}
