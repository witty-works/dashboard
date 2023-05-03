<?php

namespace App\Http\Livewire\UserFalsePositive;

use App\Models\FalsePositive;
use Illuminate\Validation\ValidationException;
use App\Http\Livewire\OrganizationFalsePositive\Form as OrganizationForm;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Form extends OrganizationForm
{
    use AuthorizesRequests;

    /**
     * The user instance.
     *
     * @var mixed
     */
    public $model;

    /**
     * Mount the component.
     *
     * @param  mixed  $model
     * @return void
     */
    public function mount($model)
    {
        $this->model = $model;

        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.user-false-positive.form');
    }

    public function storeFalsePositive()
    {
        $this->validate();

        $query = FalsePositive::query()
            ->where('user_id', $this->model->id)
            ->where('false_positive', $this->false_positive);

        if ($this->false_positive_id) {
            $query->whereNot('id', $this->false_positive_id);
            $falsePositive = FalsePositive::find($this->false_positive_id);
        }

        if (!empty($falsePositive)) {
            if ($this->model->id !== $falsePositive->user_id) {
                $message = __('guidelines.false_positive_error');
                throw ValidationException::withMessages(['false_positive' => $message]);
            }
        } else {
            if ($this->model->getFalsePositivesLimitReached()) {
                $message = __('guidelines.false_positive_limit_reached_error', ['max_count' => $this->model->getFalsePositivesCount()]);
                throw ValidationException::withMessages(['false_positive' => $message]);
            }

            $falsePositive = new FalsePositive();
        }

        $count = $query->count();
        if ($count) {
            $message = __('guidelines.false_positive_already_exists');
            throw ValidationException::withMessages(['false_positive' => $message]);
        }

        $falsePositive->false_positive = $this->false_positive;
        $falsePositive->language_code = null;
        $falsePositive->user_id = $this->model->id;

        $falsePositive->save();
        $falsePositive->dispatchEventToPosthog();

        $this->emit('saved');
        $this->resetForm();
    }
}
