<?php

namespace App\Livewire;

trait TeamsGuidelineTrait
{
    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($model, $category = null, $config = null)
    {
        $this->model = $model;

        $this->resetForm();
    }

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
    }

    public function saved()
    {
        $this->mount($this->model);
    }
}
