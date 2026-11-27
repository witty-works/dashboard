<?php

namespace Tests\Feature;

use App\Mail\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * One support address for the deployment (SUPPORT_EMAIL), and links that
 * point at the deployment itself: nothing names the old company's addresses.
 */
class SupportEmailTest extends TestCase
{
    use RefreshDatabase;

    private const SUPPORT = 'help@deployment.example';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.support_email' => self::SUPPORT]);
    }

    public function test_the_error_page_names_the_support_address(): void
    {
        config(['sentry.dsn' => 'https://public@sentry.example/1']);
        $this->app->instance('sentry', new class {
            public function getLastEventId(): string
            {
                return 'abc123';
            }
        });
        (new \Illuminate\Foundation\Exceptions\RegisterErrorViewPaths)();

        $this->assertStringContainsString('mailto:' . self::SUPPORT, view('errors.500')->render());
    }

    public function test_the_failed_word_sign_in_names_the_support_address(): void
    {
        $this->get('/word-addin?status=failed')->assertSee('mailto:' . self::SUPPORT, false);
    }

    public function test_the_invitation_email_names_the_support_address_and_no_help_centre(): void
    {
        Bus::fake();
        $owner = User::factory()->withPersonalTeam()->create();
        $this->actingAs($owner);
        $invitation = $owner->ownedTeams()->first()->teamInvitations()->create(['email' => 'new@example.org', 'role' => 'user']);

        $html = (new TeamInvitation($invitation))->render();

        $this->assertStringContainsString('mailto:' . self::SUPPORT, $html);
        $this->assertStringNotContainsString('help-center', $html);
    }

    public function test_the_dictionary_hints_link_the_deployment_itself(): void
    {
        foreach (['no_corporate_rules' => 'teams.dictionary', 'corporate_rules_none_opened' => 'teams.dictionary',
            'no_corporate_rules_user' => 'user.dictionary', 'corporate_rules_none_opened_user' => 'user.dictionary'] as $key => $route) {
            $this->assertStringContainsString('href="' . route($route) . '"', __('content.' . $key, ['url' => route($route)]));
        }
    }

    public function test_nothing_names_the_old_addresses(): void
    {
        $found = [];
        $files = (new Finder)->files()->in([resource_path('views'), resource_path('lang'), app_path()])->name(['*.php', '*.json']);
        foreach ($files as $file) {
            foreach (['support@witty.works', 'engineering@witty.works', 'sales@witty.works', 'dashboard.witty.works', 'witty.works/pricing', 'help-center'] as $old) {
                if (str_contains($file->getContents(), $old)) {
                    $found[] = $file->getRelativePathname() . ': ' . $old;
                }
            }
        }

        $this->assertSame([], $found);
    }
}
