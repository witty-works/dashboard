<?php

namespace App\Http\Livewire\UserGuidelines;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class Orthography extends Component
{
    use AuthorizesRequests;
    use CategoryTrait;

    public $disabled_categories;
    public $disabled_categories_orthography;

    protected $rules = [
        'disabled_categories_orthography' => 'nullable|boolean',
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
        $this->mountCategories($user, GuidelinesInterface::DISABLED_CATEGORIES_ORTHOGRAPHY);
    }

    public function updateLanguageGuidelinesOrthography()
    {
        return $this->updateCategories(GuidelinesInterface::DISABLED_CATEGORIES_ORTHOGRAPHY);
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.user-guidelines.orthography');
    }

    protected function getLanguageGuidelines($user)
    {
        return LanguageGuidelines::firstOrNew(['user_id' => $user->id]);
    }
}
