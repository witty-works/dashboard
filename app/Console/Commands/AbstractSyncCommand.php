<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;

abstract class AbstractSyncCommand extends Command
{
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    protected function filterQueryByIds($query, $optionName)
    {
        $idsString = $this->option($optionName);

        if ($idsString !== null) {
            $ids = explode(',', trim($idsString));
            if ($ids !== array_filter($ids, 'is_numeric')) {
                $this->error('Non integer passed as ID: ' . $idsString);
                return false;
            }
            $query->whereIn('id', $ids);
        }

        return $query;
    }

    protected function handleJob($job)
    {
        if ($this->option('e')) {
            $result = $job->handle();
            if (is_scalar($result) || is_array($result)) {
                $this->info("Executed job, got: " . json_encode($result));
            }
        } else {
            dispatch($job);
        }
    }

    protected function handleTeam(Team $team)
    {
    }

    protected function handleTeams($query = null)
    {
        $results = ['success' => [], 'failure' => []];

        $query = $query ?? Team::query();
        $query = $this->filterQueryByIds($query, 'team-ids');
        if (!$query) {
            return $results;
        }

        foreach ($query->cursor() as $team) {
            /** @var \App\Models\Team $team */

            try {
                $this->handleTeam($team);
                $results['success'][] = $team->id;
            } catch (\Exception $e) {
                $results['failure'][] = $team->id;
            }
        }

        $teamCount = count($results['success']);
        $this->info("Finished syncing $teamCount teams");

        $teamCount = count($results['failure']);
        $this->error("Failed syncing $teamCount teams");

        return $results;
    }

    protected function handleUser(User $user)
    {
    }

    public function handleUsers($query = null)
    {
        $results = ['success' => [], 'failure' => []];

        $query = $query ?? User::query();
        $query = $this->filterQueryByIds($query, 'ids');
        if (!$query) {
            return $results;
        }

        foreach ($query->cursor() as $user) {
            /** @var \App\Models\User $user */
            try {
                $this->handleUser($user);
                $results['success'][] = $user->id;
            } catch (\Exception $e) {
                $results['failure'][] = $user->id;
            }
        }

        $userCount = count($results['success']);
        $this->info("Finished syncing $userCount users");

        $userCount = count($results['failure']);
        $this->error("Failed syncing $userCount users");

        return $results;
    }
}
