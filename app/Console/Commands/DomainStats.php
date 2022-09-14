<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DomainStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:user-domains';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send statistics about domains of users';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $results = DB::select("SELECT COUNT(*) as count, RIGHT(email, LENGTH(email)-INSTR(email, '@')) as domain FROM users GROUP BY domain ORDER BY domain");
        $html = "Domain Counts (for domains that had new users in the last 24h)";
        $html .= "<ul>";

        $count = 0;
        foreach ($results as $result) {
            $created_count = DB::select("SELECT COUNT(*) as count FROM users WHERE email LIKE '%@{$result->domain}' AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY) GROUP BY created_at");
            if (empty($created_count[0])) {
                continue;
            }

            $team_count = DB::select("SELECT COUNT(*) as count FROM users WHERE email LIKE '%@{$result->domain}' GROUP BY current_team_id");

            $created_count = $created_count[0]->count;
            $team_count = $team_count[0]->count;
            $html .= "<li>{$result->domain}: {$result->count} in {$team_count} different teams (added {$created_count})</li>";

            $count++;
        }
        $html .= "</ul>";

        Mail::send([], [], function (Message $message) use ($html) {
            $message->to('sales@witty.works')
                ->subject('Dashboard Domains of Emails')
                ->from('support@witty.works')
                ->html($html);
        });

        $this->info("Send email for $count domains with new users.");
    }
}
