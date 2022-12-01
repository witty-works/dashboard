<?php

namespace App\Http\Livewire\Teams;

use Laravel\Jetstream\Http\Livewire\UpdateTeamNameForm;

class UpdateTeamNameFormCancel extends UpdateTeamNameForm
{
    public function mount($team)
    {
        $this->team = $team;

        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $this->state = $this->team->withoutRelations()->toArray();
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }
}
