<?php

namespace App\Console\Commands;

use App\Jobs\SyncUserToHubSpot;
use App\Models\User;

class SyncToHubspot extends AbstractSyncCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:sync {--ids=} {--f} {--e}';

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

        $force = $this->hasOption('f');

        $query = $force
            ? User::query()
            : User::whereNull('hubspot_id')->orWhereNull('hubspot_source');

        $query = $this->filterQueryByIds($query);
        if (!$query) {
            return 1;
        }

        $userCount = 0;
        foreach ($query->cursor() as $user) {
            /** @var \App\Models\User $user */
            $job = new SyncUserToHubSpot($user);
            if ($this->hasOption('e')) {
                $job->handle();
            } else {
                dispatch($job);
            }

            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
