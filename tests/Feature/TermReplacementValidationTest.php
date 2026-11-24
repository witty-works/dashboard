<?php

namespace Tests\Feature;

use App\Livewire\OrganizationTermReplacement\Form as OrganizationForm;
use App\Livewire\UserTermReplacement\Form as UserForm;
use App\Models\TermReplacement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The browser extension shows a term replacement's explanation and URL inside
 * third-party pages. The explanation must not carry markup and the URL must be
 * http or https, on both the organization and the personal form.
 */
class TermReplacementValidationTest extends TestCase
{
    use RefreshDatabase;

    private function signedInUser(): User
    {
        $user = User::factory()->withPersonalTeam()->create();
        $user->switchTeam($user->ownedTeams()->firstOrFail());

        return $user->refresh();
    }

    /**
     * @return array{0: class-string, 1: mixed}
     */
    private function form(string $scope): array
    {
        $user = $this->signedInUser();
        $this->actingAs($user);

        return $scope === 'organization'
            ? [OrganizationForm::class, $user->currentTeam]
            : [UserForm::class, $user];
    }

    private function submit(string $scope, string $explanation, string $url = '')
    {
        [$component, $model] = $this->form($scope);

        return Livewire::test($component, ['model' => $model])
            ->set('term', 'chairman')
            ->set('replacement', 'chair')
            ->set('explanation', $explanation)
            ->set('url', $url)
            ->call('storeTermReplacement');
    }

    public static function scopeProvider(): array
    {
        return [
            'organization' => ['organization'],
            'user' => ['user'],
        ];
    }

    public static function markupProvider(): array
    {
        $cases = [
            'start tag' => '<b>gender-neutral</b>',
            'end tag' => 'gender-neutral</p>',
            'event handler' => '<img src=x onerror=alert(1)>',
            'comment' => 'gender-neutral <!-- x -->',
            'processing instruction' => '<?xml version="1.0"?>',
        ];

        $data = [];
        foreach (self::scopeProvider() as $scope => [$scopeValue]) {
            foreach ($cases as $name => $explanation) {
                $data["$scope: $name"] = [$scopeValue, $explanation];
            }
        }

        return $data;
    }

    #[DataProvider('markupProvider')]
    public function test_explanation_with_markup_is_rejected(string $scope, string $explanation): void
    {
        $this->submit($scope, $explanation)
            ->assertHasErrors(['explanation']);

        $this->assertSame(0, TermReplacement::count());
    }

    #[DataProvider('scopeProvider')]
    public function test_the_markup_error_is_translated(string $scope): void
    {
        $this->submit($scope, '<b>x</b>')
            ->assertHasErrors(['explanation' => __('validation.no_markup', ['attribute' => __('validation.attributes.explanation')])]);
    }

    public function test_the_markup_message_exists_in_every_locale(): void
    {
        foreach (['en', 'de', 'fr'] as $locale) {
            $this->assertNotSame(
                'validation.no_markup',
                __('validation.no_markup', [], $locale),
                "validation.no_markup is missing for $locale"
            );
        }
    }

    #[DataProvider('scopeProvider')]
    public function test_a_lone_less_than_sign_is_allowed(string $scope): void
    {
        $this->submit($scope, 'Use it when a < b, or <3 people')
            ->assertHasNoErrors();

        $this->assertSame('Use it when a < b, or <3 people', TermReplacement::firstOrFail()->explanation);
    }

    public static function rejectedUrlProvider(): array
    {
        return [
            'data' => ['data://text/html,hello'],
            'file' => ['file://etc/passwd'],
            'ftp' => ['ftp://example.com/'],
            'javascript' => ['javascript:alert(1)'],
        ];
    }

    #[DataProvider('rejectedUrlProvider')]
    public function test_url_other_than_http_or_https_is_rejected(string $url): void
    {
        $this->submit('organization', 'More on this', $url)
            ->assertHasErrors(['url']);
    }

    public function test_http_and_https_urls_are_accepted(): void
    {
        $this->submit('organization', 'More on this', 'https://example.com/guide')
            ->assertHasNoErrors();

        $this->submit('user', 'More on this', 'http://example.com/guide')
            ->assertHasNoErrors();
    }
}
