<?php

namespace Database\Seeders;

use App\Models\TermReplacement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data for the browser tests (tests/browser): a signed-in team owner with a
 * member and a dictionary entry in each list, so the pages they check have
 * rows. Never in production.
 */
class BrowserTestSeeder extends Seeder
{
    public const EMAIL = 'browser@example.org';
    public const PASSWORD = 'browser-test-password';

    public function run(): void
    {
        abort_if(app()->environment('production'), 403, 'The browser test data is not for production.');

        $owner = User::factory()->withPersonalTeam()->create([
            'name' => 'Browser Owner',
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => now(),
        ]);
        $team = $owner->ownedTeams()->firstOrFail();
        $owner->switchTeam($team);
        $owner->forceFill(['license_team_id' => $team->id])->save();

        $member = User::factory()->create(['name' => 'Sam Member', 'email' => 'sam@example.org']);
        $team->users()->attach($member, ['role' => 'user']);

        foreach (['user_id' => $owner->id, 'team_id' => $team->id] as $column => $id) {
            TermReplacement::forceCreate(['term' => 'chairman', 'replacement' => 'chair', 'language_code' => 'en', $column => $id]);
        }
    }
}
