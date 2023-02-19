<?php

namespace App\Console\Commands;

use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use App\Helpers\PosthogHelper;
use Carbon\Carbon;

class StoreKpis extends AbstractSyncCommand
{
    protected $signature = 'kpis {--ids=} {--team-ids=} {--date=}';

    protected $description = 'Store KPIs (like team count and writing streaks';

    protected $date;

    public function handle()
    {
        $date = $this->option('date');
        $this->date = $date ? new Carbon($date) : Carbon::yesterday();
        $this->date = $this->date->format('Y-m-d');

        $this->handleTeams();
        $this->handleUsers();
   }

    protected function storeKpi($model, $kpi, $value)
    {
        $idColumnName = $model instanceof Team ? 'team_id' : 'user_id';

        Kpi::updateOrCreate(
            [
                $idColumnName => $model->id,
                'date' => $this->date,
                'kpi' => $kpi,
            ],
            [
                'value' => $value,
            ]
        );
    }

    protected function handleTeam(Team $team)
    {
        $this->storeKpi($team, Kpi::TEAM_COUNT, $team->getTotalUserCount());
        $this->storeKpi($team, Kpi::LICENSE_COUNT, $team->getUserLicensesCount());
        $this->storeKpi($team, Kpi::WRITING_STREAK, $this->getWritingStreak($team));
    }

    protected function handleUser(User $user)
    {
        $this->storeKpi($user, Kpi::WRITING_STREAK, $this->getWritingStreak($user));
    }

    protected function getWritingStreak($model)
    {
        $properties = $model instanceof Team
            ? PosthogHelper::getOrganizationFilter($model)
            : PosthogHelper::getUserFilter($model);

        $filter = [
            'events' => [
                [
                    'properties' => $properties,
                    'math' => 'total',
                    'id' => 'check',
                ]
            ],
            'filter_test_accounts' => false,
            'interval' => 'day',
            'date_from' => $this->date,
            'date_to' => $this->date,
        ];

        $response = PosthogHelper::fetchData($filter, PosthogHelper::getUrl());

        if (isset($response['result'][0])) {
            foreach ($response['result'][0]['data'] as $data) {
                if ($data) {
                    return 1;
                }
            }
        }

        return 0;
    }
}
