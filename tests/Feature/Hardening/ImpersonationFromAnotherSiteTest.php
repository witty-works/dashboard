<?php

namespace Tests\Feature\Hardening;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * Impersonation starts with a GET (the package's route, linked from the admin
 * page). A link or an image on another site must not switch a superadmin's
 * session into someone else's account, so a request the browser marks as
 * coming from another site is refused.
 */
class ImpersonationFromAnotherSiteTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    private function take(string $site): array
    {
        $admin = $this->verifiedSuperadmin(User::factory()->withPersonalTeam()->create());
        $user = User::factory()->withPersonalTeam()->create();
        $response = $this->actingAs($admin)
            ->withHeaders(['Sec-Fetch-Site' => $site])
            ->get('/en/impersonate/take/' . $user->id);

        return [$admin, $user, $response];
    }

    public function test_a_request_from_another_site_is_refused(): void
    {
        [$admin, , $response] = $this->take('cross-site');

        $response->assertForbidden();
        $this->assertSame($admin->id, auth()->id());
    }

    public function test_a_request_from_another_subdomain_is_refused(): void
    {
        [$admin, , $response] = $this->take('same-site');

        $response->assertForbidden();
        $this->assertSame($admin->id, auth()->id());
    }

    public function test_the_admin_pages_link_still_works(): void
    {
        [, $user, $response] = $this->take('same-origin');

        $response->assertRedirect();
        $this->assertSame($user->id, auth()->id());
    }
}
