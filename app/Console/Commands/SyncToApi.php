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
    protected $signature = 'nlp_api:sync {--ids=} {--team-ids=} {--e}';

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

        $this->handleTeams();
        $this->handleUsers();
    }

    protected function handleTeam(Team $team)
    {
        $job = new SyncOrganizationToNlpApi($team);
        $this->handleJob($job);
    }

    protected function handleUser(User $user)
    {
        $job = new SyncUserToNlpApi($user);
        $this->handleJob($job);
    }
}
