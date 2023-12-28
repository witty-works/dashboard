<?php

namespace App\Livewire\Teams;

use App\Livewire\HelpHeroTrait;
use Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;
use Money\Currency;

class PlanSummary extends Component
{
    use AuthorizesRequests;
    use HelpHeroTrait;

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

        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->resetErrorBag();

        $subscription = $this->team->subscription();
        $licenseCount = $subscription
            ? $subscription->quantity
            : $this->team->getTotalUserWithInvitationsCount();

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

        $checkOut = $this->team->redirectToCheckout($licenseCount);
        if ($checkOut) {
            return $checkOut;
        }

        $subscription = $this->team->subscription();
        if ($subscription->quantity != $licenseCount) {
            $subscription->alwaysInvoice()->updateQuantity($licenseCount);
            $subscription->syncStartRenewalAt();
            $this->dispatch('saved');
            $this->updateHelpHero();
        } else {
            $message = __('teams.license_count_did_not_change');
            throw ValidationException::withMessages(['license_count' => $message]);
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

    public function cancel()
    {
        $this->resetForm();

        return $this->render();
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
        $keys = range(max(20, $licenseCount), 150, 5);

        $count = 19;
        while ($count >= $licenseCount) {
            array_unshift($keys, $count);
            $count--;
        }

        $licenseOptions = [];
        $price = config('stripe.plans.witty_teams.price') * 100;
        $currency = new Currency(config('stripe.currency'));
        foreach ($keys as $key) {
            $licenseCount = $this->convertLicenseCountToString($key);
            $params = [
                'count' => $key,
                'amount' => Cashier::formatAmount($price * $key, $currency, config('app.locale'))
            ];
            $licenseOptions[$licenseCount] = __('teams.amount_per_year', $params);
        }

        return $licenseOptions;
    }
}
