<?php

namespace App\Http\Controllers;

use App\Auth\OAuthSigningKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Publishes the public half of Passport's token signing key as a JWK Set.
 *
 * Passport issues RS256 access tokens, so the NLP API can verify them with a
 * public key fetched over HTTP — exactly how it already fetches Microsoft's keys
 * for Office SSO tokens. Nothing secret is copied between services, and a
 * self-hoster pointing the extension at their own dashboard needs no key
 * material at all.
 *
 * The `kid` here matches the one App\Auth\AccessToken puts in the token header;
 * both come from OAuthSigningKey so they cannot drift.
 */
class JwksController extends Controller
{
    public function __construct(protected OAuthSigningKey $key)
    {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json(['keys' => [$this->key->jwk()]]);
    }
}
