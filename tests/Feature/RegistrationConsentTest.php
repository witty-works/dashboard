<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Agreeing to the documents at sign-up on the web is recorded, as it is for
 * the Word add-in, so nobody is asked twice.
 */
class RegistrationConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_sign_up_records_the_consent(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'terms');
        file_put_contents($path, "## Terms\n");
        config(['app.terms_of_service_file' => $path]);

        $this->post(route('register'), [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'Str0ng-Passw0rd!',
            'password_confirmation' => 'Str0ng-Passw0rd!',
            'terms' => true,
        ])->assertSessionHasNoErrors();

        $this->assertTrue((bool) User::where('email', 'ada@example.com')->firstOrFail()->has_consented_to_terms_of_service);
        unlink($path);
    }
}
