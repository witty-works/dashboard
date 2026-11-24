<?php

namespace App\Console\Commands;

use App\Models\OAuthClient;
use Illuminate\Console\Command;

/**
 * Create or update the public OAuth client the dashboard's write page signs in
 * with. Idempotent, like passport:extension-client, so it can run on every
 * deploy: with PASSPORT_EDITOR_CLIENT_ID set it updates that client.
 */
class CreateEditorOAuthClientCommand extends Command
{
    protected $signature = 'passport:editor-client';

    protected $description = 'Create or update the public OAuth client used by the dashboard write page';

    public function handle(): int
    {
        $redirectUri = self::redirectUri();
        $clientId = config('passport.editor.client_id');
        $client = $clientId ? OAuthClient::find($clientId) : null;

        // As for the extension: an id that resolves to nothing is an error, not
        // an invitation to mint a second client the page does not know about.
        if ($clientId && ! $client) {
            $this->error(sprintf(
                'PASSPORT_EDITOR_CLIENT_ID is set to "%s" but no such client exists.',
                $clientId
            ));
            $this->line('Clear the variable to provision a new client, or point it at the right database.');

            return self::FAILURE;
        }

        $existed = (bool) $client;
        $client ??= new OAuthClient();
        $client->forceFill([
            'name' => config('passport.editor.name'),
            // Public: the page runs in the browser, so PKCE authenticates the
            // token exchange rather than a secret.
            'secret' => null,
            'provider' => 'users',
            'redirect_uris' => [$redirectUri],
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();

        $this->info(sprintf('%s OAuth client "%s".', $existed ? 'Updated' : 'Created', $client->name));
        $this->newLine();
        $this->line('  Client ID:    ' . $client->getKey());
        $this->line('  Redirect URI: ' . $redirectUri);
        $this->newLine();
        $this->comment('Set these if they are not already:');
        $this->line('  PASSPORT_EDITOR_CLIENT_ID=' . $client->getKey());
        $this->line('  PASSPORT_FIRST_PARTY_CLIENTS: add ' . $client->getKey() . ' (no consent screen)');
        $this->line('  NLP API DASHBOARD_CLIENT_ID: add ' . $client->getKey() . ' (the API accepts its tokens)');

        return self::SUCCESS;
    }

    /**
     * The write page's callback under APP_URL: the one place it is derived,
     * for this command and for the page that sends it to /oauth/authorize.
     */
    public static function redirectUri(): string
    {
        return rtrim((string) config('app.url'), '/') . '/write/callback';
    }
}
