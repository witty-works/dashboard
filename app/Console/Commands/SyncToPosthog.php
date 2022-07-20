<?php

namespace App\Console\Commands;

use App\Events\UserCreated;
use App\Listeners\PostHogUpdateCompany;
use App\Listeners\PostHogUpdateUser;
use App\Models\User;
use App\Models\Team;
use Illuminate\Console\Command;
use Laravel\Jetstream\Events\TeamCreated;
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
        $teamFailed = 0;

        $postHogUpdateCompany = new PostHogUpdateCompany();
        foreach (Team::query()->cursor() as $team) {
            if (!$postHogUpdateCompany->handle(new TeamCreated($team))) {
                $teamFailed++;
            }

            $teamCount++;
        }

        $this->info("Finished syncing $teamCount teams (failed $teamFailed)");

        $userCount = 0;
        $userFailed = 0;

        $postHogUpdateUser = new PostHogUpdateUser();
        foreach (User::query()->cursor() as $user) {
            if (!$postHogUpdateUser->handle(new UserCreated($user))) {
                $userFailed++;
            }

            $userCount++;
        }

        $this->info("Finished syncing $userCount users (failed $userFailed)");
    }
}
