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
        'false_positive' => 'required|min:2',
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
        return view('livewire.organization-false-positive.form');
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

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $query = FalsePositive::query()
            ->where('team_id', $this->team->id)
            ->where('false_positive', $this->false_positive);

        if ($this->false_positive_id) {
            $query->whereNot('id', $this->false_positive_id);
            $falsePositive = FalsePositive::find($this->false_positive_id);

            if ($this->team->id !== $falsePositive->team_id) {
                $message = __(
                    'guidelines.false_positive_error',
                );
                throw ValidationException::withMessages(['term' => $message]);
            }
        } else {
            if ($this->team->getFalsePositivesLimitReached()) {
                $message = __(
                    'guidelines.false_positive_limit_reached_error',
                    ['max_count' => $this->team->getFalsePositivesCount()]
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
        $falsePositive->team_id = $this->team->id;
        $falsePositive->save();

        $this->emit('saved');

        $this->false_positive = '';
        $this->false_positive_id = '';
    }
}
