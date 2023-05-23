<?php

namespace App\Actions\Socialstream;

use App\Http\Controllers\OAuthController;
use App\Models\User;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use GuzzleHttp\Exception\ClientException;

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
        try {
            $provider = OAuthController::getProvider($provider, $policy);

            $user = $provider
                ->with(['policy' => $policy])
                ->user();
        } catch (ClientException $e) {
            abort(400);
        }

        $user->name = $user->nickname = $user->user['nickname'] = $user->user['name'] ?? '';
        $user->user['email'] = User::getEmailFromProvider($user->user);
        if (!empty($user->user['email'])) {
            $user->email = strtolower($user->user['email']);
        }

        return $user;
    }
}
