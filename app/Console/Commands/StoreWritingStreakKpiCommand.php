<?php

namespace App\Console\Commands;

use App\Helpers\PosthogHelper;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;

class StoreWritingStreakKpiCommand extends Command
{
    protected $signature = 'writing-streak-kpi {--ids=} {--team-ids=} {--date=} {--e}';

    protected $description = 'Store writing streak KPI';

    protected $date;

    public function handle()
    {
        $date = $this->option('date');
        $this->date = $date ? new Carbon($date) : Carbon::yesterday();
        $this->date = $this->date->format('Y-m-d');

        $html = $this->updateWritingStreaks() . "\n";
        $html .= $this->updateWritingStreaks(true);

        $this->info($html);

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to('engineering@witty.works')
                ->subject(getenv('PLATFORM_ENVIRONMENT') . ': writing streak KPI')
                ->from('support@witty.works')
                ->html(nl2br($html));
        });

        $this->info("Send email ..");
    }

    protected function getId($id)
    {
        $id = explode(':', $id);
        return array_pop($id);
    }

    protected function updateWritingStreaks($group = false)
    {
        $total = $count = 0;
        do {
            if (empty($data['next'])) {
                $offset = 0;
            } else {
                parse_str($data['next'], $result);
                $offset = $result['offset'] ?? 0;
            }

            $data = PosthogHelper::fetchWritingStreak($this->date, $offset, $group);
            if (empty($data['results'][0]['people'])) {
                break;
            }

            foreach ($data['results'][0]['people'] as $entity) {
                $total++;
                if ($group) {
                    if (empty($entity['id'])) {
                        continue;
                    }

                    $model = Team::find($this->getId($entity['id']));
                } else {
                    if (empty($entity['properties']['dashboard_id'])) {
                        continue;
                    }

                    $model = User::find($this->getId($entity['properties']['dashboard_id']));
                }

                if ($model) {
                    $count++;
                    Kpi::storeKpi($model, Kpi::WRITING_STREAK, 1, $this->date);
                }
            }
        } while (!empty($data['next']));

        if ($group) {
            $html = "Success processing $total PostHog organizations on {$this->date}\n";
            $html .= "Found $count teams with writing streaks";
        } else {
            $html = "Success processing $total PostHog persons on {$this->date}\n";
            $html .= "Found $count users with writing streaks";
        }

        return $html;
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
}
