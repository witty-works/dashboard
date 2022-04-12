<?php

namespace App\Http\Livewire\TermReplacement;

use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $term;
    public $replacement;
    public $language_code;

    protected $rules = [
        'term' => 'required|min:1',
        'replacement' => 'required|min:1|different:term',
        'language_code' => 'nullable|size:2',
    ];

    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;
    }

    public function render()
    {
        return view('livewire.term-replacement.form');
    }

    public function createTermReplacement()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $count = TermReplacement::query()
            ->where('team_id', $this->team->id)
            ->where('term', $this->term)
            ->count();
        if ($count) {
            $message = __('guidelines.term_already_exists');
            throw ValidationException::withMessages(['term' => $message]);
        }

        if ($this->team->term_replacements_limit_reached) {
            $message = __(
                'guidelines.term_replacement_limit_reached_error',
                ['max_count' => $this->team->term_replacements_count]
            );
            throw ValidationException::withMessages(['term' => $message]);
        }

        $termReplacement = new TermReplacement();
        $termReplacement->term = $this->term;
        $termReplacement->replacement = $this->replacement;
        $termReplacement->language_code = null;
        $termReplacement->team_id = $this->team->id;
        $termReplacement->save();

        $this->emit('saved');

        $this->term = '';
        $this->replacement = '';
    }
}
