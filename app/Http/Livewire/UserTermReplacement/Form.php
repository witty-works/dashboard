<?php

namespace App\Http\Livewire\UserTermReplacement;

use App\Models\TermReplacement;
use App\Http\Livewire\OrganizationTermReplacement\Form as OrganizationForm;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Form extends OrganizationForm
{
    use AuthorizesRequests;

    public $user;

    public function mount($user)
    {
        $this->user = Auth::user();
    }

    public function render()
    {
        return view('livewire.user-term-replacement.form');
    }

    public function storeTermReplacement()
    {
        $this->validate();

        $query = TermReplacement::query()
            ->where('user_id', $this->user->id)
            ->where('term', $this->term);

        if ($this->term_replacement_id) {
            $query->whereNot('id', $this->term_replacement_id);
            $termReplacement = TermReplacement::find($this->term_replacement_id);
        }

        if (!empty($termReplacement)) {
            if ($this->user->id !== $termReplacement->user_id) {
                $message = __(
                    'guidelines.term_replacement_error',
                );
                throw ValidationException::withMessages(['term' => $message]);
            }
        } else {
            if ($this->user->getTermReplacementsLimitReached()) {
                $message = __(
                    'guidelines.term_replacement_limit_reached_error',
                    ['max_count' => $this->user->getTermReplacementsCount()]
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

        $this->emoji = TermReplacement::validateEmoji($this->emoji);

        $termReplacement->term = $this->term;
        $termReplacement->replacement = $this->replacement;
        $termReplacement->language_code = null;
        $termReplacement->explanation = $this->explanation;
        $termReplacement->url = $this->url;
        $termReplacement->emoji = $this->emoji;
        $termReplacement->user_id = $this->user->id;
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
