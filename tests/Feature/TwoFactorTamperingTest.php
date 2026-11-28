<?php

namespace Tests\Feature;

use App\Livewire\Admin\Teams;
use App\Livewire\Admin\Users;
use App\Models\User;
use App\Support\SuperadminTwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\UsesTwoFactor;
use Tests\TestCase;

/**
 * A Livewire request can set any public property and call render() without
 * an action: what decides access has to hold for those requests too.
 */
class TwoFactorTamperingTest extends TestCase
{
    use RefreshDatabase;
    use UsesTwoFactor;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
    }

    public static function displayFlags(): array
    {
        return [['showingQrCode'], ['showingConfirmation'], ['showingRecoveryCodes']];
    }

    #[DataProvider('displayFlags')]
    public function test_the_client_cannot_show_the_secret_or_the_codes(string $flag): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $this->confirmTwoFactor($user, ['rc-aaaa-one', 'rc-bbbb-two']);
        $this->actingAs($user->refresh());

        $this->expectException(CannotUpdateLockedPropertyException::class);
        Livewire::test('profile.two-factor-authentication-form')->set($flag, true);
    }

    public function test_a_confirmed_setup_never_shows_its_secret(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        $secret = $this->confirmTwoFactor($user);
        $this->actingAs($user->refresh())->withSession(['auth.password_confirmed_at' => time()]);

        // Enabling again keeps the confirmed secret and asks to show its QR code.
        Livewire::test('profile.two-factor-authentication-form')
            ->call('enableTwoFactorAuthentication')
            ->assertDontSee($secret);
    }

    public static function adminComponents(): array
    {
        return [[Users::class], [Teams::class]];
    }

    #[DataProvider('adminComponents')]
    public function test_a_replayed_admin_page_refuses_a_session_without_a_code(string $component): void
    {
        $admin = $this->verifiedSuperadmin(User::factory()->withPersonalTeam()->create());
        $page = Livewire::actingAs($admin)->test($component);

        // Later, or in another session: the same snapshot, no code entered.
        SuperadminTwoFactor::forget();

        $page->call('$refresh')->assertForbidden();
    }
}
