<?php

namespace App\Console\Commands;

use App\Jobs\SyncUserToHubSpot;
use App\Models\User;

class SyncToHubspotContactsCommand extends AbstractSyncCommand
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

        $this->delay = config('hubspot.delay_per_count');

        $this->info('Queuing syncing to Hubspot ...');

        $query = null;
        if (!$this->option('f')) {
            $query = User::whereNull('hubspot_id')->orWhereNull('hubspot_source');
        }

        $this->handleUsers($query);
    }

    protected function handleUser(User $user)
    {
        $job = new SyncUserToHubSpot($user);
        $this->handleJob($job);
    }
}
