<?php

namespace App\Http\Livewire\FalsePositive;

use App\Models\FalsePositive;
use Livewire\Component;

class Show extends Component
{
    protected $listeners = ['saved'];

    public function render()
    {
        $list = FalsePositive::all()->sortByDesc('created_at');

        return view('livewire.false-positive.show', ['list' => $list]);
    }

    public function saved()
    {
        $this->render();
    }

    public function deleteItem(FalsePositive $falsePositive)
    {
        $falsePositive->delete();
    }
}
