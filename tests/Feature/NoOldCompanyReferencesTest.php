<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * The dashboard is run by whoever deploys it, so its views, copy and
 * configuration name neither Witty Works the company nor promise what its
 * commercial service promised. Links that explain a recommendation (the blog
 * posts and category pages) and the media the category data uses stay on the
 * website on purpose.
 */
class NoOldCompanyReferencesTest extends TestCase
{
    use RefreshDatabase;

    /** Links into the website that explain something, kept on purpose. */
    private const ALLOWED_LINKS = '#https://www\.witty\.works/(en|de|fr)/(blog|categories|kategorien)/[^\s"\'<)]*|https://www\.witty\.works/assets/media/[^\s"\'<)]*#';

    /** Code that names the site for reasons other issues deal with. */
    private const ELSEWHERE = [
        // Where the category data names its images; APP_MEDIA_URL moves them.
        'app/Helpers/CategoryDataHelper.php',
        'config/app.php',
        // The "only witty.works" domain list option, an older feature.
        'app/Jobs/AbstractSyncToNlpApi.php',
        'app/Livewire/OrganizationDomain/Show.php',
        // The statistics mail's address filter (#1021).
        'app/Console/Commands/StatisticsEmailCommand.php',
    ];

    public function test_views_copy_and_config_do_not_name_the_old_company(): void
    {
        $found = [];
        foreach (['resources/views', 'resources/lang', 'config', 'app'] as $dir) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($dir)));
            foreach ($files as $file) {
                if (! $file->isFile() || ! preg_match('/\.(php|json)$/', $file->getFilename())) {
                    continue;
                }
                $path = str_replace(base_path() . '/', '', $file->getPathname());
                if (in_array($path, self::ELSEWHERE, true)) {
                    continue;
                }
                foreach (file($file->getPathname()) as $number => $line) {
                    $line = preg_replace(self::ALLOWED_LINKS, '', $line);
                    if (preg_match('/witty\.works|Witty Works/', $line)) {
                        $found[] = $path . ':' . ($number + 1) . ': ' . trim($line);
                    }
                }
            }
        }

        $this->assertSame([], $found, "References to the old company:\n" . implode("\n", $found));
    }

    public function test_the_logo_in_the_menu_leads_to_the_dashboard_home(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        $html = $this->actingAs($user)->withoutLocaleRedirects()->get('/user/profile')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '#<a href="' . preg_quote(url('/'), '#') . '">\\s*<img class="wittyworks-logo"[^>]*alt="Witty, dashboard home"#',
            $html
        );
    }
}
