<?php

namespace App\Http\Livewire\UserFalsePositive;

use App\Models\FalsePositive;
use Illuminate\Validation\ValidationException;
use App\Http\Livewire\OrganizationFalsePositive\Form as OrganizationForm;

class Form extends OrganizationForm
{
    /**
     * The user instance.
     *
     * @var mixed
     */
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
    }

    public function render()
    {
        return view('livewire.user-false-positive.form');
    }

    public function storeFalsePositive()
    {
        $this->validate();

        $query = FalsePositive::query()
            ->where('user_id', $this->user->id)
            ->where('false_positive', $this->false_positive);

        if ($this->false_positive_id) {
            $query->whereNot('id', $this->false_positive_id);
            $falsePositive = FalsePositive::find($this->false_positive_id);
        }

        if (!empty($falsePositive)) {
            if ($this->user->id !== $falsePositive->user_id) {
                $message = __(
                    'guidelines.false_positive_error',
                );
                throw ValidationException::withMessages(['term' => $message]);
            }
        } else {
            if ($this->user->getFalsePositivesLimitReached()) {
                $message = __(
                    'guidelines.false_positive_limit_reached_error',
                    ['max_count' => $this->user->getFalsePositivesCount()]
                );
                throw ValidationException::withMessages(['term' => $message]);
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
        $falsePositive->user_id = $this->user->id;
        $falsePositive->save();

        $this->emit('saved');

        $this->false_positive = '';
        $this->false_positive_id = '';
    }
}
