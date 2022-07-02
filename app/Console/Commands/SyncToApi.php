<?php

namespace App\Console\Commands;

use App\Events\UserCreated;
use App\Listeners\UpdateUserGuidelines;
use App\Listeners\UpdateOrganizationGuidelines;
use App\Models\User;
use App\Models\Team;
use Illuminate\Console\Command;
use Laravel\Jetstream\Events\TeamCreated;

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
        $updateOrganizationGuidelines = new UpdateOrganizationGuidelines();
        foreach (Team::query()->cursor() as $team) {
            $updateOrganizationGuidelines->handle(new TeamCreated($team));
            $teamCount++;
        }

        $this->info("Finished syncing $teamCount teams");

        $userCount = 0;
        $updateUserGuidelines = new UpdateUserGuidelines();
        foreach (User::query()->cursor() as $user) {
            $updateUserGuidelines->handle(new UserCreated($user));
            $userCount++;
        }

        $this->info("Finished syncing $userCount users");
    }
}
