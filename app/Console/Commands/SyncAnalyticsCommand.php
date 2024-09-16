<?php

namespace App\Console\Commands;

use App\Jobs\SyncFromPosthogToDb;
use App\Models\Team;

class SyncAnalyticsCommand extends AbstractSyncCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:sync {--team-ids=} {--date=} {--interval=} {--e}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync organization analytics from Posthog to the DB';

    protected $date;
    protected $interval;

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

        $this->date = $this->option('date');
        if ($this->date && !preg_match('/^\d\d\d\d-\d\d-\d\d$/', $this->date)) {
            $this->error('Date needs to be formated as YYYY-mm-dd, got: ' . $this->date);

            return;
        }

        $this->interval = $this->option('interval');
        if (!$this->interval || ($this->interval !== 'month' &&  $this->interval !== 'week')) {
            $this->error('Interval needs to be set to either "month" or "week".');

            return;
        }

        if ($this->date === null) {
            $this->date = date('Y-m-d', strtotime('first day of previous ' . $this->interval));
        }

        $this->delay = config('posthog.delay_per_count');

        $this->info("Queuing syncing {$this->date} ({$this->interval}) from Posthog ...");

        $this->handleTeams();
    }

    protected function handleTeam(Team $team)
    {
        $job = new SyncFromPosthogToDb($team, $this->date, $this->interval);
        $this->handleJob($job);
    }
}
