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
    protected $signature = 'posthog:sync {--ids=} {--team-ids=}';

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

        $query = Team::query();
        $query = $this->filterQueryByIds($query, 'team-ids');
        if (!$query) {
            return 1;
        }

        $teamCount = 0;
        foreach ($query->cursor() as $team) {
            /** @var \App\Models\Team $team */
            dispatch(new SyncOrganizationToPosthog($team));
            $teamCount++;
        }

        $this->info("Finished syncing $teamCount teams");

        $query = User::query();
        $query = $this->filterQueryByIds($query);
        if (!$query) {
            return 1;
        }

        $userCount = 0;
        foreach ($query->cursor() as $user) {
            /** @var \App\Models\User $user */
            dispatch(new SyncUserToPosthog($user));
            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
