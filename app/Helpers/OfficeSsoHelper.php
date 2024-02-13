<?php

namespace App\Helpers;

use Exception;
use Laravel\Socialite\Two\InvalidStateException;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use SocialiteProviders\Manager\OAuth2\User;
use Symfony\Component\Uid\Uuid;

// some code taken from https://github.com/SocialiteProviders/AzureADB2C
// license: MIT
class OfficeSsoHelper
{
    /**
     * The custom Guzzle configuration options.
     *
     * @var array
     */
    protected $guzzle = [];

    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * validate id_token
     * - signature validation using firebase/jwt library.
     * - claims validation
     *   iss: MUST much iss = issuer value on metadata.
     *   aud: MUST include client_id for this client.
     *   exp: MUST time() < exp.
     *
     * @param  string  $idToken
     * @return array
     *
     * @throws Laravel\Socialite\Two\InvalidStateException
     */
    public function validateIdToken($idToken)
    {
        try {
            // payload validation
            $payload = explode('.', $idToken);
            if (empty($payload[1])) {
                throw new InvalidStateException('payload cannot be parsed');
            }

            $payloadJson = json_decode(base64_decode(str_pad(strtr($payload[1], '-_', '+/'), strlen($payload[1]) % 4, '=')), true);
            $openIdConfiguration = $this->getOpenIdConfiguration();

            // iss validation - https://learn.microsoft.com/en-us/entra/identity-platform/access-tokens#multi-tenant-applications
            if (!isset($payloadJson['tid']) || !Uuid::isValid($payloadJson['tid'])) {
                throw new InvalidStateException('tid is missing or invalid in access token');
            }
            $issuer = str_replace('{tenantid}', $payloadJson['tid'], $openIdConfiguration->issuer);
            if (strcmp($payloadJson['iss'], $issuer) !== 0) {
                throw new InvalidStateException('iss on id_token does not match issuer value on the OpenID configuration');
            }
            // aud validation
            if (!str_contains($payloadJson['aud'], config('services.microsoft_office.client_id'))) {
                throw new InvalidStateException('aud on id_token does not match the client_id for this application');
            }
            // exp validation
            if ((int) $payloadJson['exp'] < time()) {
                throw new InvalidStateException('id_token is expired');
            }

            // signature validation and return claims
            return (array) JWT::decode($idToken, JWK::parseKeySet($this->getJWTKeys($openIdConfiguration), 'RS256'));
        } catch (Exception $ex) {
            throw new InvalidStateException("Error on validating id_token. {$ex}");
        }
    }

    public function getProviderAccount($user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['sub'],
            'nickname' => $user['name'] ?? null,
            'name'     => $user['name'] ?? null,
            'email'    => $user['preferred_username'] ?? null,
        ]);
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client($this->guzzle);
        }

        return $this->httpClient;
    }

    /**
     * Get public keys to verify id_token from jwks_uri.
     *
     * @return array
     */
    private function getJWTKeys($openIdConfiguration)
    {
        $response = $this->getHttpClient()->get($openIdConfiguration->jwks_uri);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Get OpenID Configuration.
     *
     * @return mixed
     *
     * @throws Laravel\Socialite\Two\InvalidStateException
     */
    private function getOpenIdConfiguration()
    {
        $url = "https://login.microsoftonline.com/common/v2.0/.well-known/openid-configuration";

        try {
            $response = $this->getHttpClient()->get($url);
        } catch (Exception $ex) {
            throw new InvalidStateException("Error on getting OpenID Configuration. {$ex}");
        }

        return json_decode((string) $response->getBody());
    }
}
