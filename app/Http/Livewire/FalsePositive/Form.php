<?php

namespace App\Http\Livewire\FalsePositive;

use App\Models\FalsePositive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public $false_positive;
    public $language_code;

    protected $rules = [
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
        return view('livewire.false-positive.form');
    }

    public function createFalsePositive()
    {
        $this->validate();

        if (!Auth::user()->hasTeamPermission($this->team, 'edit_guidelines')) {
            abort(403);
        }

        $count = FalsePositive::query()
            ->where('false_positive', $this->false_positive)
            ->count();
        if ($count) {
            $message = __('guidelines.false_positive_already_exists');
            throw ValidationException::withMessages(['false_positive' => $message]);
        }


        $count = FalsePositive::query()
            ->where('team_id', $this->team->id)
            ->count();

        $max_count = $this->team->false_positive_count();
        if ($count >= $max_count) {
            $message = __(
                'guidelines.plan_only_allows_x_false_positives',
                ['max_count' => $max_count]
            );
            throw ValidationException::withMessages(['false_positive' => $message]);
        }

        $falsePositive = new FalsePositive();
        $falsePositive->false_positive = $this->false_positive;
        $falsePositive->language_code = null;
        $falsePositive->team_id = $this->team->id;
        $falsePositive->save();

        $this->emit('saved');

        $this->false_positive = '';
    }
}
