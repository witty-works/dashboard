<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Laravel\Passport\AccessToken;
use Laravel\Passport\Passport;

/**
 * Signs the browser extension out: revokes the access token the request was
 * made with and the refresh tokens issued alongside it.
 *
 * Only the presented token. The user's other tokens, i.e. the extension on their
 * other browsers and devices, stay valid; logging out of the dashboard is what
 * revokes everything (App\Listeners\RevokeOAuthTokens).
 *
 * The refresh tokens are the part that matters. league/oauth2-server's refresh
 * grant does not look at the access token's `revoked` flag, so revoking the
 * access token alone would leave the extension's stored refresh token able to
 * mint new access tokens for the rest of its 30 days.
 */
class RevokeTokenController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $token = $request->user()->token();

        // Anything else is a cookie-authenticated TransientToken, which has no
        // database row to revoke. The `auth:api` guard on the route only lets
        // bearer tokens through in practice, since this app never issues the
        // laravel_token cookie.
        if ($token instanceof AccessToken) {
            $accessTokenId = $token->oauth_access_token_id;

            // Through the models rather than a repository: Passport 13 deleted
            // RefreshTokenRepository and reduced TokenRepository to lookups.
            Passport::refreshTokenModel()::query()
                ->where('access_token_id', $accessTokenId)
                ->where('revoked', false)
                ->update(['revoked' => true]);

            Passport::tokenModel()::query()
                ->whereKey($accessTokenId)
                ->update(['revoked' => true]);
        }

        return response()->noContent();
    }
}
