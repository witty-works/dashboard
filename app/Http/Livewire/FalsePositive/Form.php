<?php

namespace App\Http\Livewire\FalsePositive;

use App\Models\FalsePositive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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

        $this->authorize('update', $this->team);

        $falsePositive = new FalsePositive();
        $falsePositive->false_positive = $this->false_positive;
        $falsePositive->language_code = $this->language_code;
        $falsePositive->team_id = $this->team->id;
        $falsePositive->save();

        $this->emit('saved');
    }
}
