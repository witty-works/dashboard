<?php

namespace App\Http\Controllers;

use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Two\InvalidStateException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Http\Controllers\OAuthController as BaseOAuthController;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Features;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Auth;

class OAuthController extends BaseOAuthController
{
    public function logout(string $provider, GeneratesProviderRedirect $generator, $policy = 'login')
    {
        return $generator->logout($provider, $policy);
    }

    public function redirectToProvider(Request $request, string $provider, GeneratesProviderRedirect $generator, $policy = 'login')
    {
        session()->put('socialstream.previous_url', back()->getTargetUrl());

        return $generator->generate($provider, $policy);
    }

    public function handleProviderCallback(Request $request, string $provider, ResolvesSocialiteUsers $resolver, $policy = 'login')
    {
        if ($request->has('error')) {
            return Auth::check()
                ? redirect(config('fortify.home'))->dangerBanner($request->error_description)
                : redirect()->route('register')->withErrors($request->error_description);
        }

        try {
            $providerAccount = $resolver->resolve($provider, $policy);
        } catch (InvalidStateException $e) {
            $this->invalidStateHandler->handle($e);
        }

        $account = Socialstream::findConnectedAccountForProviderAndId($provider, $providerAccount->getId());

        // Authenticated...
        if (!is_null($user = Auth::user())) {
            return $this->alreadyAuthenticated($user, $account, $provider, $providerAccount);
        }

        // Registration...
        if (FortifyFeatures::enabled(FortifyFeatures::registration()) && session()->get('socialstream.previous_url') === route('register') && !$account) {
            $user = Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->first();

            if ($user) {
                return $this->handleUserAlreadyRegistered($user, $account, $provider, $providerAccount);
            }

            return $this->register($account, $provider, $providerAccount);
        }

        if (!Features::hasCreateAccountOnFirstLoginFeatures() && !$account) {
            return redirect()->route('login')->withErrors(
                __('An account with this :Provider sign in was not found. Please register or try a different sign in method.', ['provider' => $provider])
            );
        }

        if (Features::hasCreateAccountOnFirstLoginFeatures() && !$account) {
            if (Jetstream::newUserModel()->where('email', $providerAccount->getEmail())->exists()) {
                return redirect()->route('login')->withErrors(
                    __('An account with that email address already exists. Please login to connect your :Provider account.', ['provider' => $provider])
                );
            }

            $user = $this->createsUser->create($provider, $providerAccount);

            return $this->login($user);
        }

        $user = $account->user;

        $this->updatesConnectedAccounts->update($user, $account, $provider, $providerAccount);

        $user->forceFill([
            'current_connected_account_id' => $account->id,
        ])->save();

        return $this->login($user);
    }

    /**
     * 
     * @param App\Models\User $user 
     * @param ConnectedAccount $account 
     * @param string $provider 
     * @param AbstractUser $providerAccount 
     * @return mixed 
     * @throws BindingResolutionException 
     * @throws RouteNotFoundException 
     */
    protected function alreadyAuthenticated($user, $account, $provider, $providerAccount)
    {
        if ($account && $account->user_id !== $user->id) {
            return redirect()->route('profile.show');
        }

        if (!$account) {
            $this->createsConnectedAccounts->create($user, $provider, $providerAccount);

            return redirect()->route('profile.show');
        }

        $user->updateName($providerAccount);
        $user->save();

        return redirect()->route('profile.show');
    }
}
