<?php

namespace App\Actions\Socialstream;

use App\Http\Controllers\OAuthController;
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
        $provider = OAuthController::getProvider($provider, $policy);

        $user = $provider
            ->with(['policy' => $policy])
            ->user();

        $user->name = $user->nickname = $user->user['nickname'] = $user->user['name'] ?? '';
        $user->email = $user->user['email'] = $user->user['emails'][0] ?? ($user->user['email'] ?? null);

        return $user;
    }
}
