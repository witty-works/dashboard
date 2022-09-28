<?php

namespace App\Console\Commands;

use App\Jobs\SyncUserToHubSpot;
use App\Models\User;
use Illuminate\Console\Command;

class SyncToHubspot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users to Hubspot that do not yet have an ID stored in the database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!config('hubspot.enabled')) {
            $this->info('HubSpot is not enabled ...');

            return;
        }

        $this->info('Queuing syncing to Hubspot ...');

        $userCount = 0;
        foreach (User::whereNull('hubspot_id')->cursor() as $user) {
            /** @var \App\Models\User $user */
            dispatch(new SyncUserToHubSpot($user));

            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
