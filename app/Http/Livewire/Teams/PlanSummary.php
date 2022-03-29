<?php

namespace App\Http\Livewire\Teams;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PlanSummary extends Component
{
    use AuthorizesRequests;

    const USER_LICENSES_STEPS = [
        5,
        10,
        15,
        20,
        25,
        30,
        35,
        40,
        45,
        50,
        55,
        60,
        65,
        70,
        75,
        80,
        85,
        90,
        95,
        100,
        105,
        110,
        115,
        120,
        125,
        130,
        135,
        140,
        145,
        150,
        155,
        160,
        165,
        170,
        175,
        180,
        185,
        190,
        195,
        200,
        205,
        210,
        215,
        220,
        225,
        230,
        235,
        240,
        245,
        250,
    ];

    public $user_licenses;

    protected $rules = [
        'user_licenses' => 'nullable|integer',
    ];

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

        $this->user_licenses = (int) $team->user_licenses;
    }

    public function updateTeamsUserLicenses()
    {
        $this->validate();

        if (!$this->team->subscribed() && !Gate::check('update', $this->team)) {
            abort(403);
        }

        if (!in_array($this->user_licenses, self::USER_LICENSES_STEPS)) {
            $message = __('teams.user_licenses_error');
            throw ValidationException::withMessages(['user_licenses' => $message]);
        }

        $this->team->user_licenses = (int) $this->user_licenses;

        $this->team->save();

        $this->emit('saved');
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.plan-summary');
    }
}
