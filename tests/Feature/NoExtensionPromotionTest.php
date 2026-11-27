<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The dashboard does not promote the browser extension: the write page
 * replaces it. The extension's own sign-in and API stay for those who use it.
 */
class NoExtensionPromotionTest extends TestCase
{
    use RefreshDatabase;

    public function test_there_is_no_download_page(): void
    {
        $this->withoutLocaleRedirects()->get('/download')->assertNotFound();
        $this->assertFalse(Route::has('download'));
    }

    public function test_signed_in_pages_show_no_install_banner(): void
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        $html = $this->actingAs($user)->withoutLocaleRedirects()->get('/user/profile')->assertOk()->getContent();

        foreach (['install-witty', 'upgrade-witty-version', 'witty-is-installed', 'detectBrowser', 'chrome.google.com/webstore'] as $gone) {
            $this->assertStringNotContainsString($gone, $html);
        }
    }

    public function test_the_extensions_own_sign_in_stays(): void
    {
        $this->assertTrue(Route::has('browser_login'));
    }
}
