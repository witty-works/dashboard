<?php

namespace App\Console\Commands;

use App\Models\Kpi;
use App\Models\Team;
use Carbon\Carbon;

class StoreKpisCommand extends AbstractSyncCommand
{
    protected $signature = 'kpis {--ids=} {--team-ids=} {--date=} {--e}';

    protected $description = 'Store KPIs (like team count and writing streaks)';

    protected $date;

    public function handle()
    {
        $date = $this->option('date');
        $this->date = $date ? new Carbon($date) : Carbon::yesterday();
        $this->date = $this->date->format('Y-m-d');

        $this->handleTeams();
    }

    protected function handleTeam(Team $team)
    {
        Kpi::storeKpi($team, Kpi::TEAM_COUNT, $team->getTotalUserCount(), $this->date);
        Kpi::storeKpi($team, Kpi::LICENSE_COUNT, $team->getUserLicensesCount(), $this->date);
    }
}
