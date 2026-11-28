<?php

namespace Tests\Feature\Accessibility;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Form fields need a border and a focus ring that stand out from white by at
 * least 3:1 (WCAG 1.4.11, 2.4.7). Tailwind's gray-300 border (1.5:1) and
 * indigo-200 ring (1.2:1) do not; gray-500 and indigo-700 do.
 */
class FieldStylesTest extends TestCase
{
    private function classes(string $html): array
    {
        $this->assertMatchesRegularExpression('/class="([^"]*)"/', $html);
        preg_match('/class="([^"]*)"/', $html, $match);

        return preg_split('/\s+/', trim($match[1]));
    }

    public function test_inputs_have_a_visible_border_and_focus_ring(): void
    {
        $classes = $this->classes(Blade::render('<x-input id="name" type="text" />'));

        $this->assertContains('border-gray-500', $classes);
        $this->assertContains('focus:ring-2', $classes);
        $this->assertContains('focus:ring-indigo-700', $classes);
        $this->assertNotContains('border-gray-300', $classes);
        $this->assertNotContains('focus:ring-indigo-200', $classes);
        $this->assertNotContains('focus:ring-opacity-50', $classes);
    }

    public function test_textareas_have_the_same_styles(): void
    {
        $classes = $this->classes(Blade::render('<x-input id="term" type="textarea" />'));

        $this->assertContains('border-gray-500', $classes);
        $this->assertContains('focus:ring-indigo-700', $classes);
    }

    public function test_selects_have_a_visible_border_and_focus_ring(): void
    {
        $classes = $this->classes(Blade::render('<x-select id="language" :options="[\'en\' => \'English\']" />'));

        $this->assertContains('border-gray-500', $classes);
        $this->assertContains('focus:ring-2', $classes);
        $this->assertContains('focus:ring-indigo-700', $classes);
        $this->assertNotContains('border-gray-300', $classes);
        $this->assertNotContains('focus:ring-indigo-200', $classes);
    }

    public function test_no_view_uses_the_faint_focus_rings(): void
    {
        $views = resource_path('views');
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($views));
        foreach ($iterator as $file) {
            // The analytics page is reworked separately.
            if (! str_ends_with($file->getFilename(), '.blade.php') || $file->getFilename() === 'analytics.blade.php') {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression(
                '/focus:ring-(indigo|blue)-(100|200|300)\b/',
                file_get_contents($file->getPathname()),
                $file->getPathname()
            );
        }
    }

    public function test_the_category_filter_border_is_visible(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression('/\.checkbox-wrapper \{[^}]*border: 1px solid #6b7280;/', $css);
    }
}
