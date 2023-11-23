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

        $this->info("Fetching users created ..");

        $html = "";
        $html .= "<h2>Users created by month of year</h2>";
        $html .= "<ul>";
        $users_created_by_week = DB::select("SELECT DATE_FORMAT(created_at, '%Y-%m') AS year_month_num, COUNT(*) AS count FROM users WHERE email NOT LIKE '%@witty.works' GROUP BY year_month_num ORDER BY year_month_num");
        foreach ($users_created_by_week as $result) {
            $html .= "<li>{$result->year_month_num}: {$result->count}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching teams with at least one invited/member users ..");

        $html .= "<h2>Team with at least one invited/member user</h2>";
        $html .= "<ul>";
        $subquery = "(SELECT COUNT(*) as subcount, team_id FROM team_user GROUP BY team_id) UNION ALL (SELECT COUNT(*) as subcount, team_id FROM team_invitations GROUP BY team_id)";
        $query = "SELECT SUM(subcount)+1 AS count, teams.id FROM ($subquery) AS counts INNER JOIN teams ON counts.team_id = teams.id GROUP BY teams.id";
        $team_subscriptions_created_by_week = DB::select($query);

        $team_users = [];
        foreach ($team_subscriptions_created_by_week as $result) {
            $team_users[$result->id] = $result->count;

            $team = Team::find($result->id);
            $owner = $team->owner;
            $impersonateUrl = config('app.url') . '/impersonate/take/' . $owner->id;
            $html .= "<li>{$team->name} (team id: {$result->id}, <a href=\"{$impersonateUrl}\">{$owner->email}</a>): {$result->count}</li>";
        }
        $html .= "</ul>";

        $this->info("Fetching team suscriptions created ..");

        foreach (['paid' => 1, 'free' => 0] as $label => $is_paid) {
            $html .= "<h2>Team $label subscription created by month of year</h2>";
            $html .= "<ul>";
            $team_subscriptions_created_by_week = DB::select("SELECT subscriptions.created_at, COALESCE(subscriptions.ends_at, 'not canceled') as ends_at, subscriptions.stripe_id, teams.id, teams.name, quantity FROM subscriptions INNER JOIN teams ON teams.id = subscriptions.team_id WHERE personal_team = 1 AND is_paid = $is_paid ORDER BY quantity");
            foreach ($team_subscriptions_created_by_week as $result) {
                $team = Team::find($result->id);
                $owner = $team->owner;
                $impersonateUrl = config('app.url') . '/impersonate/take/' . $owner->id;
                $licenses_used = $team_users[$result->id] ?? 0;
                $payment_method = str_contains($result->stripe_id, 'invoice') ? 'invoice' : 'credit card';
                $html .= "<li>{$result->name} (team id: {$result->id}, <a href=\"{$impersonateUrl}\">{$owner->email}</a>) {$result->created_at} - {$result->ends_at}: {$result->quantity} licenses ({$licenses_used} invited/used, {$payment_method})</li>";
            }
            $html .= "</ul>";
        }

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to('sales@witty.works')
                ->subject('Dashboard Domains of Emails')
                ->from('support@witty.works')
                ->html($html);
        });

        $this->info("Send email ..");
    }
}
