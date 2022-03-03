<?php

namespace App\Http\Livewire\OrganizationGuidelines;

use App\Models\OrganizationGuidelines;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $german_gender_ending;
    public $gendered_roles_format;
    public $store_context;
    public $preferred_variants;
    public $preferred_variants_de;
    public $preferred_variants_en;
    public $singular_they;
    public $expert_mode;
    public $disabled_categories;
    public $disabled_categories_orthography;
    public $disabled_categories_style;
    public $disabled_categories_inclusive;
    public $disabled_categories_casing;

    protected $rules = [
        'german_gender_ending' => 'nullable|string|in::in,*in,/in,_in,In,/-in',
        'gendered_roles_format' => 'nullable|string|in:both,gender_inclusive,gender_binary',
        'store_context' => 'nullable|boolean',
        'preferred_variants_de' => 'nullable|string|in:both,de_DE,de_AT,de_CH',
        'preferred_variants_en' => 'nullable|string|in:both,en_US,en_GB',
        'singular_they' => 'nullable|boolean',
        'expert_mode' => 'nullable|boolean',
        'disabled_categories_orthography' => 'nullable|boolean',
        'disabled_categories_style' => 'nullable|boolean',
        'disabled_categories_inclusive' => 'nullable|boolean',
        'disabled_categories_casing' => 'nullable|boolean',
    ];

    public $team;

    const LANGUAGES = ['en', 'de'];

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $this->german_gender_ending = $organizationRule->german_gender_ending;
        $this->gendered_roles_format = $organizationRule->gendered_roles_format;
        $this->store_context = (bool) $organizationRule->store_context;

        $this->preferred_variants = (array) json_decode($organizationRule->preferred_variants, JSON_OBJECT_AS_ARRAY);
        foreach (self::LANGUAGES as $lang) {
            $property = "preferred_variants_" . $lang;
            foreach (constant('App\Models\OrganizationGuidelines::PREFERRED_VARIANTS_' . strtoupper($lang)) as $locale => $trans_key) {
                if (in_array($locale, $this->preferred_variants)) {
                    $this->$property = $locale;
                }
            }
        }

        $this->singular_they = (bool) $organizationRule->singular_they;
        $this->expert_mode = (bool) $organizationRule->expert_mode;

        $this->disabled_categories = (array) json_decode($organizationRule->disabled_categories, JSON_OBJECT_AS_ARRAY);
        foreach (OrganizationGuidelines::DISABLED_CATEGORIES as $category) {
            $property = "disabled_categories_" . $category;
            $this->$property = in_array($category, $this->disabled_categories);
        }
    }

    public function updateOrganizationGuidelines()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $organizationRule = $this->getOrganizationGuidelines($this->team);

        $organizationRule->german_gender_ending = $this->german_gender_ending;
        $organizationRule->gendered_roles_format = $this->gendered_roles_format;
        $organizationRule->store_context = (bool) $this->store_context;

        $this->preferred_variants = [];
        foreach (self::LANGUAGES as $lang) {
            $property = "preferred_variants_" . $lang;
            if ($this->$property) {
                $this->preferred_variants[] = $this->$property;
            }
        }
        $organizationRule->preferred_variants = json_encode($this->preferred_variants);
        $organizationRule->singular_they = (bool) $this->singular_they;
        $organizationRule->expert_mode = (bool) $this->expert_mode;

        foreach (OrganizationGuidelines::DISABLED_CATEGORIES as $category) {
            $property = "disabled_categories_" . $category;
            if ($this->$property) {
                $this->disabled_categories[] = $category;
            } elseif (array_search($category, $this->disabled_categories) !== false) {
                unset($this->disabled_categories[array_search($category, $this->disabled_categories)]);
            }
        }

        $this->disabled_categories = array_unique($this->disabled_categories);
        $organizationRule->disabled_categories = json_encode($this->disabled_categories);

        $organizationRule->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.organization-guidelines.form');
    }

    protected function getOrganizationGuidelines($team)
    {
        $organizationRule = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);

        return $organizationRule;
    }
}
