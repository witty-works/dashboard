<?php

namespace App\Console\Commands;

use App\Jobs\SyncToPosthog;
use App\Models\User;
use App\Models\Team;

class SyncToPosthogCommand extends AbstractSyncCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posthog:sync {--ids=} {--team-ids=} {--e} {--f}';

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

        $this->handleTeams();
        $this->handleUsers();
    }

    protected function handleTeam(Team $team)
    {
        $job = new SyncToPosthog($team, $this->option('f'));
        $this->handleJob($job);
    }

    protected function handleUser(User $user)
    {
        $job = new SyncToPosthog($user, $this->option('f'));
        $this->handleJob($job);
    }
}
