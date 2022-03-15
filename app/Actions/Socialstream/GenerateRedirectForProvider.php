<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use Laravel\Socialite\Facades\Socialite;
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
        return Socialite::driver($provider)
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

        return redirect(Socialite::driver($provider)
            ->logout(route('dashboard')));
    }
}
