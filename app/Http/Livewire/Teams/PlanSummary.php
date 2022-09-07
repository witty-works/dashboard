<?php

namespace App\Http\Livewire\Teams;

use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;
use Money\Currency;

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
        $licenseCount = $subscription
            ? $subscription->quantity
            : $team->getTotalUserWithInvitationsCount();

        $this->licenseCount = $this->convertLicenseCountToString($licenseCount);
    }

    public function updateLicenses()
    {
        if (!Auth::user()->ownsTeam($this->team)) {
            abort(403);
        }

        $requiredLicenses = $this->team->getTotalUserWithInvitationsCount();
        $licenseOptions = $this->getLicenseOptions($requiredLicenses);
        if (!isset($licenseOptions[$this->licenseCount])) {
            $message = __('teams.license_count_error');
            throw ValidationException::withMessages(['license_count' => $message]);
        }

        $licenseCount = $this->parseLicenseCountFromString($this->licenseCount);
        if ($licenseCount < $requiredLicenses) {
            $message = __('teams.license_count_too_small_error');
            throw ValidationException::withMessages(['license_count' => $message]);
        }

        $subscription = $this->team->subscription();
        if (!$subscription) {
            return $this->team->subscribe($licenseCount)->redirect();
        }

        if ($subscription->quantity != $licenseCount) {
            $subscription->alwaysInvoice()->updateQuantity($licenseCount);
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
        return view('livewire.teams.plan-summary', ['licenseOptions' => $this->getLicenseOptions($this->team->getTotalUserWithInvitationsCount())]);
    }

    protected function convertLicenseCountToString($licenseCount)
    {
        return "$licenseCount-licenses";
    }

    protected function parseLicenseCountFromString($licenseCount)
    {
        return intval($licenseCount);
    }

    protected function getLicenseOptions($licenseCount)
    {
        $keys = range(max(10, $licenseCount), 150, 5);

        $count = 9;
        while ($count >= $licenseCount) {
            array_unshift($keys, $count);
            $count--;
        }

        $licenseOptions = [];
        foreach ($keys as $key) {
            $licenseCount = $this->convertLicenseCountToString($key);
            $params = [
                'count' => $key,
                'amount' => Cashier::formatAmount(18000 * $key, new Currency('USD'), config('app.locale'))
            ];
            $licenseOptions[$licenseCount] = __('teams.amount_per_year', $params);
        }

        return $licenseOptions;
    }
}
