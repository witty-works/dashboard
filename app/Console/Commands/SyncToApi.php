<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrganizationToNlpApi;
use App\Jobs\SyncUserToNlpApi;
use App\Models\User;
use App\Models\Team;

class SyncToApi extends AbstractSyncCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nlp_api:sync {--ids=} {--team-ids=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync organization rules to the NLP API';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Syncing to NLP API ...');

        $query = Team::query();
        $query = $this->filterQueryByIds($query, 'team-ids');
        if (!$query) {
            return 1;
        }

        $teamCount = 0;
        foreach (Team::query()->cursor() as $team) {
            /** @var \App\Models\Team $team */
            dispatch(new SyncOrganizationToNlpApi($team));
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
            dispatch(new SyncUserToNlpApi($user));
            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
