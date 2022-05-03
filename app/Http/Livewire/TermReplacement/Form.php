<?php

namespace App\Http\Livewire\TermReplacement;

use App\Models\TermReplacement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use function Emoji\is_single_emoji;

class Form extends Component
{
    use AuthorizesRequests;

    public $term;
    public $term_replacement_id;
    public $replacement;
    public $explanation;
    public $url;
    public $emoji;
    public $language_code;

    protected $listeners = ['edit'];

    protected $rules = [
        'term_replacement_id' => 'int|nullable',
        'term' => 'required|min:1',
        'replacement' => 'required|min:1|different:term',
        'explanation' => 'required_with:url,emoji|max:100',
        'url' => 'nullable|url|max:250',
        'emoji' => 'nullable',
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

    public function edit(TermReplacement $termReplacement)
    {
        $this->term_replacement_id = $termReplacement->id;
        $this->term = $termReplacement->term;
        $this->replacement = $termReplacement->replacement;
        $this->explanation = $termReplacement->explanation;
        $this->url = $termReplacement->url;
        $this->emoji = $termReplacement->emoji;

        return view('livewire.term-replacement.form');
    }

    public function storeTermReplacement()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $query = TermReplacement::query()
            ->where('team_id', $this->team->id)
            ->where('term', $this->term);

        if ($this->term_replacement_id) {
            $query->whereNot('id', $this->term_replacement_id);
            $termReplacement = TermReplacement::find($this->term_replacement_id);

            if ($this->team->id !== $termReplacement->team_id) {
                $message = __(
                    'guidelines.term_replacement_error',
                );
                throw ValidationException::withMessages(['term' => $message]);
            }
        } else {
            if ($this->team->getTermReplacementsLimitReached()) {
                $message = __(
                    'guidelines.term_replacement_limit_reached_error',
                    ['max_count' => $this->team->getTermReplacementsCount()]
                );
                throw ValidationException::withMessages(['term' => $message]);
            }

            $termReplacement = new TermReplacement();
        }

        $count = $query->count();
        if ($count) {
            $message = __('guidelines.term_already_exists');
            throw ValidationException::withMessages(['term' => $message]);
        }

        if ($this->emoji !== null && !is_single_emoji($this->emoji)) {
            $message = __('guidelines.emoji_invalid_format');
            throw ValidationException::withMessages(['emoji' => $message]);
        }

        $termReplacement->term = $this->term;
        $termReplacement->replacement = $this->replacement;
        $termReplacement->language_code = null;
        $termReplacement->explanation = $this->explanation;
        $termReplacement->url = $this->url;
        $termReplacement->emoji = $this->emoji;
        $termReplacement->team_id = $this->team->id;
        $termReplacement->save();

        $this->emit('saved');

        $this->term_replacement_id = '';
        $this->term = '';
        $this->replacement = '';
        $this->explanation = '';
        $this->url = '';
        $this->emoji = '';
    }
}
