<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Mails go out in the language the person uses the dashboard in, including
 * Laravel's own password reset and address verification mails (#44).
 */
class MailLanguageTest extends TestCase
{
    use RefreshDatabase;

    public static function mails(): array
    {
        return [
            'password reset, German' => ['de', fn () => new ResetPassword('token'), ['Passwort zurücksetzen', 'Falls du kein neues Passwort angefordert hast']],
            'password reset, French' => ['fr', fn () => new ResetPassword('token'), ['Réinitialiser le mot de passe', "Si vous n’avez pas demandé"]],
            'verification, German' => ['de', fn () => new VerifyEmail(), ['E-Mail-Adresse bestätigen', 'Falls du kein Konto erstellt hast']],
            'verification, French' => ['fr', fn () => new VerifyEmail(), ["Confirmer l’adresse e-mail", "Si vous n’avez pas créé de compte"]],
        ];
    }

    #[DataProvider('mails')]
    public function test_the_mail_is_written_in_the_language(string $locale, \Closure $notification, array $expected): void
    {
        $user = User::factory()->create(['language' => $locale]);
        app()->setLocale($locale);

        $mail = $notification()->toMail($user);
        $html = (string) $mail->render();

        $this->assertNotSame('Reset your password', $mail->subject);
        $this->assertNotSame('Verify your email address', $mail->subject);
        foreach ($expected as $text) {
            $this->assertStringContainsString(e($text), $html);
        }
        $this->assertStringNotContainsString('Regards,', $html);
        $this->assertStringNotContainsString('All rights reserved.', $html);
        $this->assertStringNotContainsString('having trouble clicking', $html);
    }

    public function test_a_notification_uses_the_persons_language_whatever_the_current_one(): void
    {
        Notification::fake();
        $user = User::factory()->create(['language' => 'fr']);
        app()->setLocale('en');

        $user->notify(new ResetPassword('token'));

        Notification::assertSentTo($user, ResetPassword::class, fn ($n, $channels, $notifiable, $locale) => $locale === 'fr');
    }

    public function test_an_unsupported_language_falls_back_to_the_current_one(): void
    {
        $this->assertNull(User::factory()->create(['language' => 'xx'])->preferredLocale());
        $this->assertNull(User::factory()->create(['language' => null])->preferredLocale());
    }
}
