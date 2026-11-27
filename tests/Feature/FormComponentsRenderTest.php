<?php

namespace Tests\Feature;

use App\Livewire\UserDomain\Form as UserDomainForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/**
 * Form fields are Blade components (<x-input>, <x-select>, <x-checkbox>). A
 * directive inside a component tag stops Blade from compiling the tag, which
 * then reaches the browser as an unknown element: no field at all.
 */
class FormComponentsRenderTest extends TestCase
{
    use RefreshDatabase;

    public static function guestPages(): array
    {
        return [
            'login' => ['/login', ['email', 'password']],
            'register' => ['/register', ['name', 'email', 'password', 'password_confirmation']],
            'forgot password' => ['/forgot-password', ['email']],
            'reset password' => ['/reset-password/some-token', ['password', 'password_confirmation']],
        ];
    }

    #[DataProvider('guestPages')]
    public function test_guest_forms_render_their_fields(string $url, array $ids): void
    {
        $html = $this->withoutLocaleRedirects()->get($url)->assertOk()->getContent();

        $this->assertStringNotContainsString('<x-', $html);
        foreach ($ids as $id) {
            $this->assertMatchesRegularExpression('/<input[^>]*id="' . $id . '"/', $html);
        }
    }

    public function test_a_field_with_an_error_is_marked_and_described(): void
    {
        $html = $this->withoutLocaleRedirects()
            ->withSession(['errors' => (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['email' => 'Wrong.']))])
            ->get('/login')->getContent();

        $this->assertStringContainsString('aria-invalid="true"', $this->field($html, 'email'));
        $this->assertStringContainsString('aria-describedby="email-error"', $this->field($html, 'email'));
        $this->assertStringNotContainsString('aria-invalid', $this->field($html, 'password'));
        // The message the field points at exists.
        $this->assertMatchesRegularExpression('/<li[^>]*id="email-error"[^>]*>\s*Wrong\./', $html);
    }

    public function test_a_livewire_field_with_an_error_is_marked(): void
    {
        Bus::fake();
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);

        $html = Livewire::test(UserDomainForm::class, ['model' => $user])
            ->set('domain', '')
            ->call('storeDomain')
            ->assertHasErrors('domain')
            ->html();

        $this->assertStringNotContainsString('<x-', $html);
        $this->assertStringContainsString('aria-invalid="true"', $this->field($html, 'domain'));
    }

    /** The <input> or <textarea> tag with this id. */
    private function field(string $html, string $id): string
    {
        $this->assertMatchesRegularExpression('/<(input|textarea)\b[^>]*\bid="' . $id . '"[^>]*>/', $html);
        preg_match('/<(input|textarea)\b[^>]*\bid="' . $id . '"[^>]*>/', $html, $match);

        return $match[0];
    }

    public function test_no_component_tag_holds_a_directive(): void
    {
        $found = [];
        foreach ((new Finder)->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
            preg_match_all('/<x-[\w.-]+\b(?:"[^"]*"|\'[^\']*\'|[^>"\'])*?\s@[a-z]+/s', $file->getContents(), $matches);
            foreach ($matches[0] as $match) {
                $found[] = $file->getRelativePathname() . ': ' . preg_replace('/\s+/', ' ', substr($match, 0, 80));
            }
        }

        $this->assertSame([], $found);
    }
}
