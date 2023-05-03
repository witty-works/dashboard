<?php

namespace App\Http\Livewire\OrganizationFalsePositive;

use App\Models\FalsePositive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $false_positive;
    public $false_positive_id;
    public $language_code;

    protected $listeners = ['edit'];

    protected $rules = [
        'false_positive_id' => 'int|nullable',
        'false_positive' => 'required|string|min:2|max:250',
        'language_code' => 'nullable|string|size:2',
    ];

    /**
     * The team instance.
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
        return view('livewire.organization-false-positive.form');
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->false_positive_id = '';
        $this->false_positive = '';
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function edit(FalsePositive $falsePositive)
    {
        $this->false_positive_id = $falsePositive->id;
        $this->false_positive = $falsePositive->false_positive;

        return $this->render();
    }

    public function storeFalsePositive()
    {
        $this->validate();

        $user = Auth::user();
        if (!$user->hasTeamPermission($this->model, 'edit_guidelines')) {
            abort(403);
        }

        $query = FalsePositive::query()
            ->where('team_id', $this->model->id)
            ->where('false_positive', $this->false_positive);

        if ($this->false_positive_id) {
            $query->whereNot('id', $this->false_positive_id);
            $falsePositive = FalsePositive::find($this->false_positive_id);
        }

        if (!empty($falsePositive)) {
            if ($this->model->id !== $falsePositive->team_id) {
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
        $falsePositive->team_id = $this->model->id;

        $falsePositive->save();
        $falsePositive->dispatchEventToPosthog();

        $this->emit('saved');
        $this->resetForm();
    }
}
