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
        $query = $query ?? Team::query();
        $query = $this->filterQueryByIds($query, 'team-ids');
        if (!$query) {
            return -1;
        }

        $teamCount = 0;
        foreach ($query->cursor() as $team) {
            /** @var \App\Models\Team $team */
            $this->handleTeam($team);
            $teamCount++;
        }

        return $teamCount;
    }

    protected function handleUser(User $user)
    {
    }

    public function handleUsers($query = null)
    {
        $query = $query ?? User::query();
        $query = $this->filterQueryByIds($query, 'ids');
        if (!$query) {
            return -1;
        }

        $userCount = 0;
        foreach ($query->cursor() as $user) {
            /** @var \App\Models\User $user */
            $this->handleUser($user);
            $userCount++;
        }

        return $userCount;
    }
}
