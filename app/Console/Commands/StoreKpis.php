<?php

namespace App\Console\Commands;

use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use App\Helpers\PosthogHelper;
use Carbon\Carbon;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

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

        $html = "";
        $results = $this->handleTeams();
        $html .= $this->getHtml($results, 'teams');
        $results = $this->handleUsers();
        $html .= $this->getHtml($results, 'users');

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to('engineering@witty.works')
                ->subject('KPIs')
                ->from('support@witty.works')
                ->html($html);
        });

        $this->info("Send email ..");
    }

    protected function getHtml($results, $modelName)
    {
        $successful = count($results['success']);
        $html = "<h2>Success syncing $successful $modelName</h2>";

        $failed = count($results['failure']);
        if ($failed) {
            $html .= "<h2>Failed syncing $failed $modelName</h2>";
            $html .= "<ul>";
            foreach ($results['failure'] as $id) {
                $html .= "<li>$modelName: $id</li>";
            }
            $html .= "</ul>";
        }

        return $html;
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
