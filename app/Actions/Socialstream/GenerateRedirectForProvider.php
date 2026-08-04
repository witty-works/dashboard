<?php

namespace App\Actions\Socialstream;

use App\Http\Controllers\OAuthController;
use App\Contracts\SocialAuth\GeneratesProviderRedirect;
use Symfony\Component\HttpFoundation\RedirectResponse;
use GuzzleHttp\Exception\ClientException;

class GenerateRedirectForProvider implements GeneratesProviderRedirect
{
    /**
     * Generates the redirect for a given provider.
     *
     * @param  string  $provider
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generate(string $provider, $policy = 'login'): RedirectResponse
    {
        try {
            $provider = OAuthController::getProvider($provider, $policy);

            return $provider
                ->with(['policy' => $policy])
                ->redirect();
        } catch (ClientException $e) {
            abort(400);
        }
    }
}
