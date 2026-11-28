<?php

namespace Tests\Feature\Accessibility;

use Tests\TestCase;

/**
 * At 320 CSS pixels wide, pages must not scroll sideways (WCAG 1.4.10).
 */
class ReflowTest extends TestCase
{
    public function test_the_language_settings_selects_shrink_to_fit(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        preg_match_all('/\.guidelines-form-section-dropdown \{([^}]*)\}/', $css, $rules);
        $width = implode('', array_filter($rules[1], fn ($rule) => str_contains($rule, 'width: 385px')));

        $this->assertNotSame('', $width);
        $this->assertStringContainsString('max-width: 100%;', $width);
    }
}
