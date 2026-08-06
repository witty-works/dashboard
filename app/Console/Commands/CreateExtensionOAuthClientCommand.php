<?php

namespace App\Console\Commands;

use App\Models\OAuthClient;
use Illuminate\Console\Command;

/**
 * Provisions the public OAuth client used by the browser extensions.
 *
 * Idempotent, so it is safe to run on every deploy: given
 * EXTENSION_OAUTH_CLIENT_ID it updates that client in place rather than
 * creating a second one, which matters because the client ID is baked into the
 * shipped extension and cannot be changed without a store release.
 */
class CreateExtensionOAuthClientCommand extends Command
{
    protected $signature = 'passport:extension-client';

    protected $description = 'Create or update the public OAuth client used by the browser extensions';

    public function handle(): int
    {
        $redirectUris = config('passport.extension.redirect_uris');

        if (empty($redirectUris)) {
            $this->error('EXTENSION_OAUTH_REDIRECT_URIS is empty; refusing to create a client that can never complete a login.');

            return self::FAILURE;
        }

        $clientId = config('passport.extension.client_id');
        $client = $clientId ? OAuthClient::find($clientId) : null;

        // A configured ID that resolves to nothing is an error, not an
        // invitation to create a second client. Silently creating one would
        // mint a fresh UUID while the shipped extension keeps sending the old
        // one, and every login would fail with an unhelpful "client not found"
        // long after this command reported success.
        if ($clientId && ! $client) {
            $this->error(sprintf(
                'EXTENSION_OAUTH_CLIENT_ID is set to "%s" but no such client exists.',
                $clientId
            ));
            $this->line('Clear the variable to provision a new client, or point it at the right database.');

            return self::FAILURE;
        }

        $existed = (bool) $client;
        $client ??= new OAuthClient();

        $client->forceFill([
            'name' => config('passport.extension.name'),
            // A public client. The extension ships to end users, so any secret
            // in it is readable by anyone who unzips the bundle — PKCE is what
            // authenticates the token exchange instead.
            'secret' => null,
            'provider' => 'users',
            // Passport 13 replaced the `redirect` string and the
            // personal_access_client/password_client booleans with these two
            // array columns. Naming the grants explicitly also keeps the client
            // to exactly the two we use: no password grant, no client
            // credentials, no device code.
            'redirect_uris' => $redirectUris,
            'grant_types' => ['authorization_code', 'refresh_token'],
            'revoked' => false,
        ])->save();

        $this->info(sprintf('%s OAuth client "%s".', $existed ? 'Updated' : 'Created', $client->name));

        $this->newLine();
        $this->line('  Client ID:     ' . $client->getKey());
        $this->line('  Redirect URIs: ' . implode(', ', $redirectUris));
        $this->newLine();

        // Both of these have to be set for the client to actually work: the
        // first so this command updates rather than duplicates on the next
        // deploy, the second so the extension skips the consent screen.
        $this->comment('Set these in .env if they are not already:');
        $this->line('  EXTENSION_OAUTH_CLIENT_ID=' . $client->getKey());
        $this->line('  PASSPORT_FIRST_PARTY_CLIENTS=' . $client->getKey());

        return self::SUCCESS;
    }
}
