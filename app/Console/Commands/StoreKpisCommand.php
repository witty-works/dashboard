<?php

namespace App\Console\Commands;

use App\Jobs\SyncWritingStreakPosthog;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

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

        $html = "";
        $results = $this->handleTeams();
        $html .= $this->getHtml($results, 'teams');
        $results = $this->handleUsers();
        $html .= $this->getHtml($results, 'users');

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to('engineering@witty.works')
                ->subject(getenv('PLATFORM_ENVIRONMENT') . ': KPIs')
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

    protected function handleTeam(Team $team)
    {
        Kpi::storeKpi($team, Kpi::TEAM_COUNT, $team->getTotalUserCount(), $this->date);
        Kpi::storeKpi($team, Kpi::LICENSE_COUNT, $team->getUserLicensesCount(), $this->date);

        $job = new SyncWritingStreakPosthog($team, $this->date);
        $this->handleJob($job);
    }

    protected function handleUser(User $user)
    {
        $job = new SyncWritingStreakPosthog($user, $this->date);
        $this->handleJob($job);
    }
}
