<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The statistics mail counts every account, and a team without an owner
 * does not stop it.
 */
class StatisticsEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_counts_every_account_and_survives_a_team_without_owner(): void
    {
        // The array transport keeps the mail, so its counts can be read.
        config(['mail.default' => 'array', 'app.support_email' => 'help@deployment.example']);
        $owner = User::factory()->withPersonalTeam()->create(['email' => 'someone@witty.works']);
        $team = $owner->ownedTeams()->firstOrFail();
        $team->users()->attach(User::factory()->create(), ['role' => 'user']);
        Team::whereKey($team->id)->update(['user_id' => 999999]);

        $this->artisan('statistics')->assertSuccessful();

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $messages);
        $html = $messages[0]->getOriginalMessage()->getHtmlBody();
        // Both accounts, the one at witty.works included, made this month.
        $this->assertStringContainsString('<li>' . now()->format('Y-m') . ': 2</li>', $html);
        $this->assertStringContainsString('no owner', $html);
    }
}
