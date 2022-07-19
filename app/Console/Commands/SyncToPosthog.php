<?php

namespace App\Console\Commands;

use App\Events\UserSync;
use App\Events\TeamSync;
use App\Models\User;
use App\Models\Team;
use Illuminate\Console\Command;
use PostHog\PostHog;

class SyncToPosthog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posthog:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users and organization to Posthog';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!config('posthog.enabled')) {
            $this->info('Posthog is not enabled ...');

            return;
        }

        PostHog::init(
            config('posthog.api_key'),
            ['host' => config('posthog.host')],
        );

        $this->info('Syncing to Posthog ...');

        $teamCount = 0;
        foreach (Team::query()->cursor() as $team) {
            event(new TeamSync($team));
            $teamCount++;
        }

        $this->info("Finished syncing $teamCount teams");

        $userCount = 0;
        foreach (User::query()->cursor() as $user) {
            event(new UserSync($user));
            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
