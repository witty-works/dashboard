<?php

namespace Tests\Feature;

use App\Models\OAuthClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * php artisan passport:editor-client: the public PKCE client the dashboard's
 * write page signs in with.
 */
class EditorOAuthClientCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_public_pkce_client_for_the_page(): void
    {
        config(['app.url' => 'https://dashboard.example.com', 'passport.editor.client_id' => null]);

        $this->artisan('passport:editor-client')->assertExitCode(0);

        $client = OAuthClient::where('name', config('passport.editor.name'))->sole();
        $this->assertNull($client->secret);
        $this->assertSame(['https://dashboard.example.com/write/callback'], $client->redirect_uris);
        $this->assertEqualsCanonicalizing(['authorization_code', 'refresh_token'], $client->grant_types);
    }

    public function test_it_updates_the_configured_client_instead_of_creating_another(): void
    {
        config(['app.url' => 'https://dashboard.example.com', 'passport.editor.client_id' => null]);
        $this->artisan('passport:editor-client')->assertExitCode(0);
        $client = OAuthClient::where('name', config('passport.editor.name'))->sole();

        config(['app.url' => 'https://new.example.com', 'passport.editor.client_id' => $client->getKey()]);
        $this->artisan('passport:editor-client')->assertExitCode(0);

        $this->assertSame(1, OAuthClient::where('name', config('passport.editor.name'))->count());
        $this->assertSame(['https://new.example.com/write/callback'], $client->fresh()->redirect_uris);
    }

    public function test_it_refuses_a_configured_id_that_does_not_exist(): void
    {
        config(['passport.editor.client_id' => '00000000-0000-0000-0000-000000000000']);

        $this->artisan('passport:editor-client')->assertExitCode(1);
    }
}
