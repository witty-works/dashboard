<?php

namespace App\Livewire\UserTermReplacement;

use App\Models\TermReplacement;
use App\Livewire\OrganizationTermReplacement\Form as OrganizationForm;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Form extends OrganizationForm
{
    use AuthorizesRequests;

    public $model;

    public function mount($model)
    {
        $this->model = $model;

        $this->resetForm();
    }

    public function render()
    {
        $this->cleanValues();

        $params = ['language_codes' => $this->getLanguageCodes()];
        return view('livewire.user-term-replacement.form', $params);
    }

    public function storeTermReplacement()
    {
        $this->validate();

        if (Auth::user()->id !== $this->model->id) {
            abort(403);
        }

        $this->cleanValues();

        $query = TermReplacement::query()
            ->where('user_id', $this->model->id)
            ->where('term', $this->term);

        if ($this->language_code) {
            $query->where(function ($q) {
                $q->whereNull('language_code')
                    ->orWhere('language_code', '')
                    ->orWhere('language_code', $this->language_code);
            });
        }

        if ($this->term_replacement_id) {
            $query->whereNot('id', $this->term_replacement_id);
            $termReplacement = TermReplacement::find($this->term_replacement_id);
        }

        if ($query->exists()) {
            $message = __('guidelines.term_already_exists');
            throw ValidationException::withMessages(['term' => $message]);
        }

        if (!empty($termReplacement)) {
            if ($this->model->id !== $termReplacement->user_id) {
                $message = __('guidelines.term_replacement_error');
                throw ValidationException::withMessages(['term' => $message]);
            }
        } else {

            $termReplacement = new TermReplacement();
        }

        $this->emoji = TermReplacement::validateEmoji($this->emoji);

        $this->handleMatchingType();

        $termReplacement->term = $this->term;
        $termReplacement->replacement = $this->replacement;
        $termReplacement->explanation = $this->explanation;
        $termReplacement->url = $this->url;
        $termReplacement->emoji = $this->emoji;
        $termReplacement->language_code = $this->language_code;
        $termReplacement->word_type = $this->word_type;
        $termReplacement->user_id = $this->model->id;

        $termReplacement->save();
        $termReplacement->dispatchEventToPosthog();

        $this->dispatch('saved');
        $this->resetForm();
    }
}
