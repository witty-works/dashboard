<?php

namespace App\Console\Commands;

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
    protected $description = 'Send statistics about users, teams and subscriptions';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("Fetching data ..");

        $this->info("Fetching domain counts ..");

        $results = DB::select("SELECT COUNT(*) as count, RIGHT(email, LENGTH(email)-INSTR(email, '@')) as domain FROM users GROUP BY domain ORDER BY domain");
        $html = "<h2>Domain Counts (for domains that had new users in the last 24h)</h2>";
        $html .= "<ul>";

        foreach ($results as $result) {
            $created_count = DB::select("SELECT COUNT(*) as count FROM users WHERE email LIKE '%@{$result->domain}' AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY created_at");
            if (empty($created_count[0])) {
                continue;
            }

            $team_count = DB::select("SELECT COUNT(DISTINCT current_team_id) as count FROM users WHERE email LIKE '%@{$result->domain}'");

            $created_count = $created_count[0]->count;
            $team_count = $team_count[0]->count;
            $html .= "<li>{$result->domain}: {$result->count} in {$team_count} different teams (added {$created_count})</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching users created ..");

        $html .= "<h2>Users created by week of year</h2>";
        $html .= "<ul>";
        $users_created_by_week = DB::select("SELECT DATE_FORMAT(created_at, '%Y-%v') AS year_week_num, COUNT(*) AS count FROM users WHERE email NOT LIKE '%@witty.works' GROUP BY year_week_num ORDER BY year_week_num");
        foreach ($users_created_by_week as $result) {
            $html .= "<li>{$result->year_week_num}: {$result->count}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching teams with at least one invited/member users ..");

        $html .= "<h2>Team with at least one invited/member user</h2>";
        $html .= "<ul>";
        $team_subscriptions_created_by_week = DB::select("SELECT SUM(subcount)+1 AS count, team_id, name FROM (SELECT COUNT(*) as subcount, team_id FROM team_user GROUP BY team_id UNION SELECT COUNT(*) as subcount, team_id FROM team_invitations GROUP BY team_id) AS counts INNER JOIN teams ON counts.team_id = teams.id GROUP BY team_id, name");
        foreach ($team_subscriptions_created_by_week as $result) {
            $html .= "<li>{$result->name} (id: {$result->team_id}): {$result->count}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching team suscriptions created ..");

        $html .= "<h2>Team subscription created by week of year</h2>";
        $html .= "<ul>";
        $team_subscriptions_created_by_week = DB::select("SELECT DATE_FORMAT(subscriptions.created_at, '%Y-%v') AS year_week_num, teams.id, teams.name, quantity FROM subscriptions INNER JOIN teams ON teams.id = subscriptions.team_id WHERE personal_team = 1 ORDER BY teams.id");
        foreach ($team_subscriptions_created_by_week as $result) {
            $html .= "<li>{$result->name} (id: {$result->id}) - {$result->year_week_num}: {$result->quantity}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching paid users created ..");

        $html .= "<h2>Paid users created by week of year</h2>";
        $html .= "<ul>";
        $paid_users_created_by_week = DB::select("SELECT DATE_FORMAT(users.created_at, '%Y-%v') AS year_week_num, teams.id, teams.name, COUNT(*) AS count FROM users INNER JOIN teams ON users.current_team_id = teams.id WHERE email NOT LIKE '%@witty.works' AND EXISTS (SELECT * FROM subscriptions WHERE subscriptions.team_id = users.current_team_id AND personal_team = 1) GROUP BY teams.id, teams.name, year_week_num ORDER BY teams.id, year_week_num");
        foreach ($paid_users_created_by_week as $result) {
            $html .= "<li>{$result->name} (id: {$result->id}) - {$result->year_week_num}: {$result->count}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching paid users invited created ..");

        $html .= "<h2>Paid invited users created by week of year</h2>";
        $html .= "<ul>";
        $invited_paid_users_created_by_week = DB::select("SELECT DATE_FORMAT(team_invitations.created_at, '%Y-%v') AS year_week_num, teams.id, teams.name, COUNT(*) AS count FROM team_invitations INNER JOIN teams ON team_invitations.team_id = teams.id INNER JOIN subscriptions ON teams.id = subscriptions.team_id WHERE email NOT LIKE '%@witty.works' AND personal_team = 1 GROUP BY teams.id, teams.name, year_week_num ORDER BY teams.id, year_week_num");
        foreach ($invited_paid_users_created_by_week as $result) {
            $html .= "<li>{$result->name} (id: {$result->id}) - {$result->year_week_num}: {$result->count}</li>";
        }
        $html .= "</ul>";

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to('sales@witty.works')
                ->subject('Dashboard Domains of Emails')
                ->from('support@witty.works')
                ->html($html);
        });

        $this->info("Send email ..");
    }
}
