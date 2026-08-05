<?php

namespace App\Actions\SocialAuth;

use App\Http\Controllers\OAuthController;
use Laravel\Socialite\Contracts\User;
use App\Models\User as ModelUser;
use App\Contracts\SocialAuth\ResolvesSocialiteUsers;
use GuzzleHttp\Exception\ClientException;

class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     */
    public function resolve(string $provider, $policy = 'login'): User
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
        $user->user['email'] = ModelUser::getEmailFromProvider($user->user);
        if (!empty($user->user['email'])) {
            $user->email = strtolower($user->user['email']);
        }

        $user->data = [];

        if (!empty($user->user['extension_termsOfUseConsentDateTime'])) {
            $user->attributes['has_consented_to_terms_of_service'] = $user->user['extension_termsOfUseConsentDateTime'];
        }

        if (!empty($user->user['extension_MailingConsented'])) {
            $user->attributes['has_consented_to_mailing'] = $user->user['extension_MailingConsented'] === 'Yes';
        }

        return $user;
    }
}
