<?php

namespace App\Helpers;

use App\Http\Controllers\OAuthController;
use App\Models\ConnectedAccount;
use App\Models\User as AppUser;
use Exception;
use Laravel\Socialite\Two\InvalidStateException;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use SocialiteProviders\Manager\OAuth2\User;
use Symfony\Component\Uid\Uuid;

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

    public function decodeIdToken($idToken)
    {
        // payload validation
        $payload = explode('.', $idToken);
        if (empty($payload[1])) {
            throw new InvalidStateException('payload cannot be parsed');
        }

        return json_decode(base64_decode(str_pad(strtr($payload[1], '-_', '+/'), strlen($payload[1]) % 4, '=')), true);
    }

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
            $payloadJson = $this->decodeIdToken($idToken);

            // A deployment serves one organisation: with its tenant set,
            // accept only that tenant's tokens. Checked before anything is
            // fetched, and again below, once the signature is verified.
            $tenantId = config('services.microsoft_office.tenant_id');
            if (!empty($tenantId) && (!is_string($payloadJson['tid'] ?? null) || strcasecmp($payloadJson['tid'], $tenantId) !== 0)) {
                throw new InvalidStateException('tid is not the configured tenant');
            }

            // aud validation: the token has to be for this application.
            // Checked before anything is fetched.
            $audience = (array) ($payloadJson['aud'] ?? []);
            if (!in_array(config('services.microsoft_office.client_id'), $audience, true)) {
                throw new InvalidStateException('aud on id_token does not match the client_id for this application');
            }

            $openIdConfiguration = $this->getOpenIdConfiguration();

            // iss validation - https://learn.microsoft.com/en-us/entra/identity-platform/access-tokens#multi-tenant-applications
            if (!isset($payloadJson['tid']) || !Uuid::isValid($payloadJson['tid'])) {
                throw new InvalidStateException('tid is missing or invalid in access token');
            }
            $issuer = str_replace('{tenantid}', $payloadJson['tid'], $openIdConfiguration->issuer);
            if (strcmp($payloadJson['iss'], $issuer) !== 0) {
                throw new InvalidStateException('iss on id_token does not match issuer value on the OpenID configuration');
            }
            // exp validation
            if ((int) $payloadJson['exp'] < time()) {
                throw new InvalidStateException('id_token is expired');
            }

            // signature validation and return claims
            $claims = (array) JWT::decode($idToken, JWK::parseKeySet($this->getJWTKeys($openIdConfiguration), 'RS256'));
            if (!empty($tenantId) && (!is_string($claims['tid'] ?? null) || strcasecmp($claims['tid'], $tenantId) !== 0)) {
                throw new InvalidStateException('tid is not the configured tenant');
            }

            return $claims;
        } catch (\Throwable $ex) {
            // A malformed token can raise an Error (a TypeError, say) as well.
            throw new InvalidStateException("Error on validating id_token. {$ex}");
        }
    }

    public function getProviderAccount($user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['sub'],
            'nickname' => $user['name'] ?? null,
            'name'     => $user['name'] ?? null,
            'email'    => $this->verifiedEmail($user),
        ]);
    }

    /**
     * The token's email address, if the tenant verified its domain.
     *
     * Tokens are accepted from any Entra tenant, and Microsoft documents
     * `email` and `preferred_username` as values a tenant's admin can set, so
     * neither identifies anyone on its own. `xms_edov: true` says the tenant
     * verified the email's domain; it and `email` are optional claims the app
     * registration has to issue.
     */
    public function verifiedEmail(array $claims): ?string
    {
        if (($claims['xms_edov'] ?? null) !== true || empty($claims['email'])) {
            return null;
        }

        return strtolower($claims['email']);
    }

    /**
     * The dashboard user a validated token stands for: the one this Microsoft
     * account is linked to (`sub`, which Microsoft keeps unique and immutable
     * per user and application), or else the one with the verified email.
     */
    public function findUser(array $claims): ?AppUser
    {
        if (!empty($claims['sub'])) {
            $account = ConnectedAccount::where('provider', OAuthController::OFFICE_PROVIDER)
                ->where('provider_id', $claims['sub'])
                ->first();
            if ($account?->user) {
                return $account->user;
            }
        }

        $email = $this->verifiedEmail($claims);

        return $email === null ? null : AppUser::where('email', $email)->first();
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
