<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
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
        $user = Socialite::driver($provider)->with(['policy' => $policy])->user();

        if ($provider === 'azureadb2c') {
            $user->nickname = $user->user['nickname'] = $user->user['name'] ?? '';
            $user->name = $user->user['name'] = trim($user->user['given_name'] ?? '' . ' ' . $user->user['family_name'] ?? '');
            if (empty($user->user['name'])) {
                $user->name = $user->user['name'] = $user->user['nickname'];
            }

            $user->email = $user->user['email'] = $user->user['emails'][0] ?: ($user->user['email'] ?: null);
        }

        return $user;
    }
}
