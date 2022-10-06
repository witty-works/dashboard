<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use HubSpot\Factory;
use HubSpot\Client\Conversations\VisitorIdentification\ApiException;
use HubSpot\Client\Conversations\VisitorIdentification\Model\IdentificationTokenGenerationRequest;
use Illuminate\Support\Facades\Session;

class HubSpotIdentificationToken
{
    public function handle(Request $request, Closure $next)
    {
        if (config('hubspot.enabled') && $request->user() && !Session::has('hubspot_identification_token')) {
            $client = Factory::createWithAccessToken(config('hubspot.access_token'));

            $identificationTokenGenerationRequest = new IdentificationTokenGenerationRequest([
                'email' => $request->user()->email,
                'firstName' => $request->user()->first_name,
                'lastName' => $request->user()->last_name,
            ]);

            try {
                $apiResponse = $client->conversations()->visitorIdentification()->generateApi()->generateToken($identificationTokenGenerationRequest);
                Session::put('hubspot_identification_token', $apiResponse->getToken());
            } catch (ApiException $e) {
            }
        }

        return $next($request);
    }
}
