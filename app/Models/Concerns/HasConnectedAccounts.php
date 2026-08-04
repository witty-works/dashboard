<?php

namespace App\Models\Concerns;

use App\Models\ConnectedAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Absorbed from joelbutcher/socialstream, which was archived upstream in
 * December 2025 and never supported Laravel 13. The connected-account model is
 * now referenced directly instead of through the package's model registry.
 *
 * @property Collection $connectedAccounts
 */
trait HasConnectedAccounts
{
    /**
     * Determine if the user owns the given connected account.
     */
    public function ownsConnectedAccount(mixed $connectedAccount): bool
    {
        return $this->id == optional($connectedAccount)->user_id;
    }

    /**
     * Determine if the user has a specific account type.
     */
    public function hasTokenFor(string $provider): bool
    {
        return $this->connectedAccounts->contains('provider', Str::lower($provider));
    }

    /**
     * Attempt to retrieve the token for a given provider.
     */
    public function getTokenFor(string $provider, mixed $default = null): mixed
    {
        if ($this->hasTokenFor($provider)) {
            return $this->connectedAccounts
                ->where('provider', Str::lower($provider))
                ->first()
                ->token;
        }

        return $default;
    }

    /**
     * Attempt to find a connected account that belongs to the user,
     * for the given provider and ID.
     */
    public function getConnectedAccountFor(string $provider, string $id): mixed
    {
        return $this->connectedAccounts
            ->where('provider', $provider)
            ->where('provider_id', $id)
            ->first();
    }

    /**
     * Get all the connected accounts belonging to the user.
     */
    public function connectedAccounts(): HasMany
    {
        return $this->hasMany(ConnectedAccount::class, 'user_id', $this->getAuthIdentifierName());
    }
}
