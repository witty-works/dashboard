<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrganizationToNlpApi;
use App\Jobs\SyncUserToNlpApi;
use App\Models\User;
use App\Models\Team;
use Illuminate\Console\Command;

class SyncToApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nlp_api:sync';

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

        $teamCount = 0;
        foreach (Team::query()->cursor() as $team) {
            /** @var \App\Models\Team $team */
            dispatch(new SyncOrganizationToNlpApi($team));
            $teamCount++;
        }

        $this->info("Finished syncing $teamCount teams");

        $userCount = 0;
        foreach (User::query()->cursor() as $user) {
            /** @var \App\Models\User $user */
            dispatch(new SyncUserToNlpApi($user));
            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
