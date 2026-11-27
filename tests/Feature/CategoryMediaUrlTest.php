<?php

namespace Tests\Feature;

use App\Helpers\CategoryDataHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The category icons and example images in storage/app/data are hosted on
 * the website. A deployment can serve its own copy by setting
 * APP_MEDIA_URL; the links to the website's explanations stay as they are.
 */
class CategoryMediaUrlTest extends TestCase
{
    use RefreshDatabase;

    private const WEBSITE_MEDIA = 'https://www.witty.works/assets/media/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->forgetLoadedData();
    }

    protected function tearDown(): void
    {
        $this->forgetLoadedData();
        parent::tearDown();
    }

    public function test_images_load_from_the_website_by_default(): void
    {
        $urls = $this->urls(CategoryDataHelper::loadTableData('categories'));

        $this->assertNotEmpty(array_filter($urls, fn ($url) => str_starts_with($url, self::WEBSITE_MEDIA)));
    }

    public function test_images_load_from_the_configured_media_url(): void
    {
        config(['app.media_url' => 'https://media.example.org/witty/']);

        $urls = $this->urls(CategoryDataHelper::loadTableData('diversity_dimension_drivers'));

        $this->assertSame([], array_values(array_filter($urls, fn ($url) => str_starts_with($url, self::WEBSITE_MEDIA))));
        $this->assertNotEmpty(array_filter($urls, fn ($url) => str_starts_with($url, 'https://media.example.org/witty/')));
        // The explanations on the website are not images and stay there.
        $this->assertNotEmpty(array_filter($urls, fn ($url) => str_starts_with($url, 'https://www.witty.works/en/categories/')));
    }

    public function test_the_content_security_policy_allows_the_configured_media_host(): void
    {
        config([
            'app.media_url' => 'https://media.example.org/witty',
            'security-headers.csp.enabled' => true,
            'security-headers.csp.report_only' => false,
        ]);

        $policy = $this->actingAs(User::factory()->withPersonalTeam()->create())
            ->get('/user/profile')
            ->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression('#img-src [^;]*https://media\.example\.org(?=[ ;])#', $policy);
        $this->assertDoesNotMatchRegularExpression('#img-src [^;]*www\.witty\.works#', $policy);
    }

    public function test_an_empty_media_url_keeps_the_website(): void
    {
        putenv('APP_MEDIA_URL=');
        try {
            $config = require base_path('config/app.php');
        } finally {
            putenv('APP_MEDIA_URL');
        }

        $this->assertSame(self::WEBSITE_MEDIA, $config['media_url']);
    }

    /** @return list<string> */
    private function urls(iterable $rows): array
    {
        $urls = [];
        $rows = collect($rows)->toArray();
        array_walk_recursive($rows, function ($value) use (&$urls) {
            if (is_string($value) && str_starts_with($value, 'http')) {
                $urls[] = $value;
            }
        });

        return $urls;
    }

    private function forgetLoadedData(): void
    {
        (fn () => static::$tableData = [])->bindTo(null, CategoryDataHelper::class)();
    }
}
