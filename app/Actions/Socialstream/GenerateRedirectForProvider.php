<?php

namespace App\Actions\Socialstream;

use App\Http\Controllers\OAuthController;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
        $provider = OAuthController::getProvider($provider, $policy);

        return $provider
            ->with(['policy' => $policy])
            ->redirect();
    }
    /**
     * Generates the logout for a given provider.
     *
     * @param  string  $provider
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logout(string $provider)
    {
        Session::flush();

        Auth::logout();

        $provider = OAuthController::getProvider($provider);

        return redirect($provider->logout(route('dashboard')));
    }
}
