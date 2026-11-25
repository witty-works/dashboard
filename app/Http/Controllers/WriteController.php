<?php

namespace App\Http\Controllers;

use App\Console\Commands\CreateEditorOAuthClientCommand;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

/**
 * The write page: Witty's editor and a prompt, signed in with the dashboard's
 * own OAuth tokens (resources/js/write.js does the sign-in). Also renders the
 * OAuth callback, which the same script handles.
 */
class WriteController extends Controller
{
    public function show(): View
    {
        $apiUrl = (string) config('app.nlp_api_public_url');
        $contact = (string) config('app.write_contact');

        return view('write', [
            'configured' => $apiUrl !== '' && (string) config('passport.editor.client_id') !== '',
            'config' => [
                'apiUrl' => $apiUrl,
                'clientId' => (string) config('passport.editor.client_id'),
                'authorizeUrl' => route('passport.authorizations.authorize'),
                'tokenUrl' => route('passport.token'),
                'redirectUri' => CreateEditorOAuthClientCommand::redirectUri(),
                // Where the script returns after the callback, without its code.
                'pageUrl' => route('write'),
                'llmEnabled' => (bool) config('app.llm_enabled'),
                'contact' => $contact,
                'lang' => app()->getLocale(),
            ],
            // What the script shows; translated here, set as text there.
            'messages' => [
                'noAccess' => __('content.write_no_access', ['contact' => $contact]),
                'reload' => __('content.write_reload'),
                'editorLabel' => __('content.write_editor_label'),
                'signInFailed' => __('content.write_sign_in_failed'),
                'rewriting' => __('content.write_rewriting'),
                'rewriteFailed' => __('content.write_rewrite_failed'),
            ],
        ]);
    }
}
