<?php

namespace App\Console\Commands;

use App\Listeners\UpdateOrganizationGuidelines;
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

        $count = 0;
        foreach (Team::query()->cursor() as $team) {
            UpdateOrganizationGuidelines::updateRules($team);
            $count++;
        }

        $this->info("Finished syncing $count teams");
    }
}
