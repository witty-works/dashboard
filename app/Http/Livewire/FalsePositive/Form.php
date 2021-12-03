<?php

namespace App\Http\Livewire\FalsePositive;

use App\Models\FalsePositive;
use Livewire\Component;

class Form extends Component
{
    public $false_positive;
    public $language_code;

    protected $rules = [
        'false_positive' => 'required|min:2',
        'language_code' => 'nullable|size:2',
    ];

    public function render()
    {
        return view('livewire.false-positive.form');
    }

    public function createFalsePositive()
    {
        $this->validate();

        $falsePositive = new FalsePositive();
        $falsePositive->false_positive = $this->false_positive;
        $falsePositive->language_code = $this->language_code;
        $falsePositive->team_id = auth()->user()->currentTeam->id;
        $falsePositive->save();

        $this->emit('saved');
    }
}
