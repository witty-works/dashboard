<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Identity of the bearer of the current token.
 *
 * The extension needs the signed-in user's email to label the UI. Passport's
 * access tokens carry only a subject and scopes — league/oauth2-server builds
 * the JWT itself and Passport exposes no hook for extra claims — so the email
 * is served here rather than embedded in the token. That is the better trade
 * anyway: the token stays small, and a renamed or re-emailed user is reflected
 * immediately instead of at the next token refresh.
 */
class UserInfoController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }
}
