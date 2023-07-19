<?php

namespace App\Actions\Socialstream;

use App\Http\Controllers\OAuthController;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
    public function generate(string $provider, $policy = 'login')
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
