<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrganizationToPosthog;
use App\Jobs\SyncUserToPosthog;
use App\Models\User;
use App\Models\Team;

class SyncToPosthog extends AbstractSyncCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posthog:sync {--ids=} {--team-ids=} {--e}';

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

        $this->info('Queuing syncing to Posthog ...');

        $teamCount = $this->handleTeams();
        $this->info("Finished syncing $teamCount teams");

        $userCount = $this->handleUsers();

        $this->info("Finished syncing $userCount users");
    }

    protected function handleTeam(Team $team)
    {
        $job = new SyncOrganizationToPosthog($team);
        $this->handleJob($job);
    }

    protected function handleUser(User $user)
    {
        $job = new SyncUserToPosthog($user);
        $this->handleJob($job);
    }
}
