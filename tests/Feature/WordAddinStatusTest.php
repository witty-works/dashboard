<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The page the Word add-in's sign-in dialog ends on: the dashboard sends the
 * add-in a status (OAuthController), and the add-in opens this page with it.
 */
class WordAddinStatusTest extends TestCase
{
    public function test_invite_only_says_there_is_no_invitation(): void
    {
        $this->get('/word-addin?status=invite_only')
            ->assertOk()
            ->assertSee(__('content.invite_only_sign_up_refused'))
            ->assertDontSee(__('content.witty_word_addin_text'), false);
    }

    public function test_failed_keeps_its_message(): void
    {
        $this->get('/word-addin?status=failed')
            ->assertOk()
            ->assertSee(__('content.witty_word_addin_text_failed'), false);
    }
}
