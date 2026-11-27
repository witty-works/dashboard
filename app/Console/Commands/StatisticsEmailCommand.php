<?php

namespace App\Console\Commands;

use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StatisticsEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send statistics about users and teams';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Fetching data ..");

        $this->info("Fetching domain counts ..");

        $this->info("Fetching users created ..");

        $html = "";
        $html .= "<h2>Users created by month of year</h2>";
        $html .= "<ul>";
        $users_created_by_week = DB::select("SELECT DATE_FORMAT(created_at, '%Y-%m') AS year_month_num, COUNT(*) AS count FROM users GROUP BY year_month_num ORDER BY year_month_num");
        foreach ($users_created_by_week as $result) {
            $html .= "<li>{$result->year_month_num}: {$result->count}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching teams with at least one invited/member users ..");

        $html .= "<h2>Team with at least one invited/member user</h2>";
        $html .= "<ul>";
        $subquery = "(SELECT COUNT(*) as subcount, team_id FROM team_user GROUP BY team_id) UNION ALL (SELECT COUNT(*) as subcount, team_id FROM team_invitations GROUP BY team_id)";
        $query = "SELECT SUM(subcount)+1 AS count, teams.id FROM ($subquery) AS counts INNER JOIN teams ON counts.team_id = teams.id GROUP BY teams.id";
        $teams_with_members = DB::select($query);

        foreach ($teams_with_members as $result) {
            $team = Team::find($result->id);
            $owner = $team?->owner;
            $name = e($team?->name ?? '');
            // A team whose owner is gone still counts, without a link.
            $who = $owner
                ? '<a href="' . e(config('app.url') . '/impersonate/take/' . $owner->id) . '">' . e($owner->email) . '</a>'
                : 'no owner';
            $html .= "<li>{$name} (team id: {$result->id}, {$who}): {$result->count}</li>";
        }
        $html .= "</ul>";

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to(config('app.support_email'))
                ->subject('Dashboard Domains of Emails')
                ->from(config('mail.from.address'))
                ->html($html);
        });

        $this->info("Send email ..");
    }
}
