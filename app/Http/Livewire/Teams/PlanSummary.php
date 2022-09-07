<?php

namespace App\Http\Livewire\Teams;

use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class PlanSummary extends Component
{
    use AuthorizesRequests;

    public $team;
    public $licenseCount;

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $subscription = $this->team->subscription();
        $this->licenseCount = $subscription
            ? $subscription->quantity
            : $team->getTotalUserWithInvitationsCount();
    }

    public function updateLicenses()
    {
        if (!Auth::user()->ownsTeam($this->team)) {
            abort(403);
        }

        $requiredLicenses = $this->team->getTotalUserWithInvitationsCount();
        if (!in_array($this->licenseCount, $this->getLicenseOptions($requiredLicenses))) {
            $message = __('teams.license_count_error');
            throw ValidationException::withMessages(['license_count' => $message]);
        }

        if ($this->licenseCount < $requiredLicenses) {
            $message = __('teams.license_count_too_small_error');
            throw ValidationException::withMessages(['license_count' => $message]);
        }

        $subscription = $this->team->subscription();
        if (!$subscription) {
            return $this->team->subscribe($this->licenseCount)->redirect();
        }

        if ($subscription->quantity != $this->licenseCount) {
            $subscription->alwaysInvoice()->updateQuantity($this->licenseCount);
            $subscription->syncStartRenewalAt();
            $this->emit('saved');
        }
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.teams.plan-summary', ['licenseOptions' => $this->getLicenseOptions($this->licenseCount)]);
    }

    protected function getLicenseOptions($licenseCount)
    {
        $licenseOptions = range(max(5, $licenseCount), 200, 5);

        $count = 4;
        while ($count >= $licenseCount) {
            array_unshift($licenseOptions, $count);
            $count--;
        }

        return $licenseOptions;
    }
}
