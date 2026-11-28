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
        // The page appends paths (`v2.0/auth`), so it ends in one slash.
        $apiUrl = (string) config('app.nlp_api_public_url');
        $apiUrl = $apiUrl === '' ? '' : rtrim($apiUrl, '/') . '/';
        $contact = (string) config('app.support_email');

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
                // The NLP API refuses the prompt where the person's licence
                // team, whose settings it applies (SyncUserToNlpApi), has LLM
                // alternatives off (SyncOrganizationToNlpApi forces them).
                'llmEnabled' => config('app.llm_enabled') && (bool) auth()->user()?->licenseTeam?->llm_alternatives,
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
                'llmRefused' => __('content.write_llm_refused'),
            ],
        ]);
    }
}
