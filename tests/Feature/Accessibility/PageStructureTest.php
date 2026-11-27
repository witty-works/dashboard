<?php

namespace Tests\Feature\Accessibility;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Titles, headings and landmarks: every page has its own title and an h1
 * that comes before any other heading (WCAG 2.4.2, 1.3.1); the navigation
 * and the footer are outside <main>; one skip link, reachable at every
 * screen width, leads to the main content (WCAG 2.4.1).
 */
class PageStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $team = $user->ownedTeams()->firstOrFail();
        $user->forceFill(['license_team_id' => $team->id])->save();
        $user->switchTeam($team);

        return $user->refresh();
    }

    /** The markup inside the one <main> element of the page. */
    private function main(string $html): string
    {
        $this->assertSame(1, preg_match_all('/<main\b/', $html), 'one <main> element');
        preg_match('/<main\b[^>]*>(.*)<\/main>/s', $html, $match);

        return $match[1];
    }

    private function firstHeading(string $html): string
    {
        $this->assertMatchesRegularExpression('/<h[1-6]\b/', $html);
        preg_match('/<h([1-6])\b/', $html, $match);

        return 'h' . $match[1];
    }

    private function assertNoEmptyOrNestedHeadings(string $html): void
    {
        $this->assertDoesNotMatchRegularExpression('/<h([1-6])\b[^>]*>\s*<\/h\1>/', $html, 'empty heading');
        $this->assertDoesNotMatchRegularExpression('/<h([1-6])\b[^>]*>(?:(?!<\/h\1>).)*<h[1-6]\b/s', $html, 'heading inside a heading');
    }

    public static function guestPages(): array
    {
        return [
            'login' => ['/login', 'content.log_in'],
            'register' => ['/register', 'content.register'],
            'forgot password' => ['/forgot-password', 'content.forgot_your_password'],
            'reset password' => ['/reset-password/some-token', 'content.reset_password'],
        ];
    }

    #[DataProvider('guestPages')]
    public function test_guest_pages_have_a_title_and_an_h1(string $url, string $title): void
    {
        $html = $this->withoutLocaleRedirects()->get($url)->assertOk()->getContent();

        $this->assertStringContainsString('<title>' . e(__($title)) . '</title>', $html);
        $this->assertMatchesRegularExpression('/<h1\b[^>]*>\s*' . preg_quote(e(__($title)), '/') . '\s*<\/h1>/', $html);
        $this->assertSame('h1', $this->firstHeading($this->main($html)));
    }

    public function test_the_confirm_password_page_has_a_title_and_an_h1(): void
    {
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()
            ->get('/user/confirm-password')->assertOk()->getContent();

        $this->assertStringContainsString('<title>' . e(__('content.confirm_password')) . '</title>', $html);
        $this->assertSame('h1', $this->firstHeading($this->main($html)));
    }

    public function test_the_verify_email_page_has_a_title_and_an_h1(): void
    {
        $user = $this->signedInUser();
        $user->forceFill(['email_verified_at' => null])->save();

        $html = $this->actingAs($user)->withoutLocaleRedirects()
            ->get('/email/verify')->assertOk()->getContent();

        $this->assertStringContainsString('<title>' . e(__('content.verify_email_title')) . '</title>', $html);
        $this->assertSame('h1', $this->firstHeading($this->main($html)));
    }

    public function test_the_guest_footer_is_outside_main(): void
    {
        $html = $this->withoutLocaleRedirects()->get('/login')->getContent();

        $this->assertStringContainsString('<footer', $html);
        $this->assertStringNotContainsString('<footer', $this->main($html));
    }

    public static function appPages(): array
    {
        return [
            'profile' => ['/user/profile'],
            'team' => ['/team/show'],
            'academy' => ['/user/academy'],
            'write' => ['/write'],
            'personal dictionary' => ['/user/language/dictionary'],
            'team dictionary' => ['/team/language/dictionary'],
            'team ignored words' => ['/team/language/ignored-words'],
            'personal privacy settings' => ['/user/language/privacy-settings'],
            'team language settings' => ['/team/language/language-settings'],
        ];
    }

    #[DataProvider('appPages')]
    public function test_navigation_and_footer_are_outside_main(string $url): void
    {
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get($url)->assertOk()->getContent();
        $main = $this->main($html);

        $this->assertStringContainsString('<nav', $html);
        $this->assertStringNotContainsString('<nav', $main);
        $this->assertStringNotContainsString('<footer', $main);
        $this->assertMatchesRegularExpression('/<main\b[^>]*id="maincontent"/', $html);
        $this->assertSame(1, substr_count($html, 'id="maincontent"'));
    }

    #[DataProvider('appPages')]
    public function test_one_skip_link_comes_before_the_navigation(string $url): void
    {
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get($url)->getContent();

        $this->assertSame(1, substr_count($html, 'class="skip-link"'));
        $this->assertMatchesRegularExpression('/<a class="skip-link" href="#maincontent">/', $html);
        $this->assertLessThan(strpos($html, '<nav'), strpos($html, 'class="skip-link"'));
    }

    public function test_the_analytics_page_keeps_one_skip_link_and_target(): void
    {
        // It still brings its own navigation and #maincontent, inside <main>.
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/user/analytics')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'class="skip-link"'));
        $this->assertSame(1, substr_count($html, 'id="maincontent"'));
    }

    public function test_the_menu_button_is_hidden_where_the_full_menu_shows(): void
    {
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get('/user/academy')->getContent();

        $this->assertMatchesRegularExpression('/<div class="[^"]*\blg:hidden\b[^"]*">\s*<button[^>]*aria-label="' . preg_quote(e(__('content.toggle_mobile_navigation')), '/') . '"/', $html);
    }

    #[DataProvider('appPages')]
    public function test_the_h1_comes_first_and_headings_are_not_empty_or_nested(string $url): void
    {
        $html = $this->actingAs($this->signedInUser())->withoutLocaleRedirects()->get($url)->getContent();
        $main = $this->main($html);

        $this->assertSame('h1', $this->firstHeading($main));
        $this->assertNoEmptyOrNestedHeadings($main);
    }

    public function test_the_team_page_h1_does_not_depend_on_the_member_manager(): void
    {
        $user = $this->signedInUser();
        $this->actingAs($user);

        // The manager is also on the profile page, below that page's h1.
        $manager = \Livewire\Livewire::test(\App\Livewire\Teams\TeamMemberManagerHelpHero::class, ['team' => $user->currentTeam])->html();
        $page = $this->withoutLocaleRedirects()->get('/team/show')->assertOk()->getContent();

        $this->assertStringNotContainsString('<h1', $manager);
        $this->assertSame(1, substr_count($page, '<h1'));
        $this->assertMatchesRegularExpression('/<h1\b[^>]*>\s*' . preg_quote(e(__('content.manage_members')), '/') . '\s*<\/h1>/', $page);
    }
}
