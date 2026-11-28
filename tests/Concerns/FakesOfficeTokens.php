<?php

namespace Tests\Concerns;

use App\Helpers\OfficeSsoHelper;

/**
 * Office SSO tokens for tests: Microsoft's signature check is replaced by a
 * stub that accepts the token as the given claims. The using class defines
 * CLIENT_ID.
 */
trait FakesOfficeTokens
{
    /** A token whose signature, issuer and expiry the helper accepts as these claims. */
    private function token(array $claims, bool $validated = true): string
    {
        $claims += ['aud' => self::CLIENT_ID, 'sub' => 'sub-attacker', 'tid' => '11111111-1111-1111-1111-111111111111'];
        $encode = fn (array $part) => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');
        $token = $encode(['alg' => 'none']) . '.' . $encode($claims) . '.sig';

        $helper = \Mockery::mock(OfficeSsoHelper::class)->makePartial();
        if ($validated) {
            $helper->shouldReceive('validateIdToken')->with($token)->atLeast()->once()->andReturn($claims);
        } else {
            $helper->shouldReceive('validateIdToken')->never();
        }
        $this->app->instance(OfficeSsoHelper::class, $helper);

        return $token;
    }

}
