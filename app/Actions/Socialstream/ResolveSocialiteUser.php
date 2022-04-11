<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use Laravel\Socialite\Facades\Socialite;

class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     *
     * @param  string  $provider
     * @return \Laravel\Socialite\AbstractUser
     */
    public function resolve($provider, $policy = 'login')
    {
        $provider = Socialite::driver($provider)
            ->with(['policy' => $policy]);

        if (self::isBrowserLogin($policy)) {
            $provider->setScopes(config('services.azureadb2c.scope'));
        }

        $user = $provider
            ->user();

        $user->name = $user->nickname = $user->user['nickname'] = $user->user['name'] ?? '';
        $user->email = $user->user['email'] = $user->user['emails'][0] ?: ($user->user['email'] ?: null);

        return $user;
    }

    static public function isBrowserLogin($policy = null)
    {
        $browserLoginPolicies = ['browser_login', 'browser_register'];

        $request = request();
        foreach ($browserLoginPolicies as $browserLoginPolicy) {
            if ($request->url() === route('oauth.callback', ['provider' => 'azureadb2c', 'policy' => $browserLoginPolicy])) {
                $policy = $browserLoginPolicy;
                break;
            }
        }

        return in_array($policy, $browserLoginPolicies);
    }
}
