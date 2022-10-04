<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUserToNlpApi;
use App\Models\ConnectedAccount;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Socialite\Two\InvalidStateException;
use Socialite;

class UserGuidelinesApiController extends Controller
{
    public function deleteDomain(Request $request)
    {
        $user = $this->getUser($request);
        $domain = Domain::validateDomain($request->get('domain'));

        $result = Domain::where('domain', $domain)
            ->where('user_id', $user->id)->delete();

        if (!$result) {
            abort(404);
        }

        dispatch(new SyncUserToNlpApi($user, 'high'));

        return response()->noContent();
    }

    public function putDomain(Request $request)
    {
        $user = $this->getUser($request);
        $domain = Domain::validateDomain($request->get('domain'));

        Domain::upsert(
            [
                ['user_id' => $user->id, 'domain' => $domain],
            ],
            ['user_id', 'domain']
        );

        dispatch(new SyncUserToNlpApi($user, 'high'));

        return response()->noContent();
    }

    protected function getUser(Request $request)
    {
        $accessToken = $request->bearerToken();
        $provider = Socialite::driver('azureadb2c');

        try {
            $socialiteUser = $provider->getUserByToken($accessToken);
            $connectedUser = ConnectedAccount::where('provider_id', $socialiteUser->getId())->first();
            if (!$connectedUser instanceof ConnectedAccount) {
                throw new InvalidStateException('Connected user not found');
            }
        } catch (InvalidStateException $e) {
            abort(403);
        }

        return $connectedUser->user;
    }
}
