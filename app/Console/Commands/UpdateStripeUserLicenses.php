<?php

namespace App\Console\Commands;

use App\Mail\RawMailable;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class UpdateStripeUserLicenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:update-users-licenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Iterates over all subscriptions and updates the user license count';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Updating subscriptions ...');

        $query = Subscription::query()
            ->where(function ($query) {
                $query->where('ends_at', '<', Carbon::now())
                    ->orWhereNull('ends_at');
            })
            ->whereDate('update_user_licenses_at', '>=', Carbon::now());

        $subscriptions = $query
            ->limit(100)
            ->get();

        $messages = [];
        foreach ($subscriptions as $subscription) {
            $team = $subscription->owner;
            $oldQuantity = $subscription->quantity;
            if ($oldQuantity != $team->total_user_licenses_count) {
                $subscription->updateQuantity($team->total_user_licenses_count);
                $messages[] = sprintf('Updated "%s" (%d) from %d to %d', $team->name, $team->id, $oldQuantity, $subscription->quantity);
            } else {
                $messages[] = sprintf('Checked "%s" (%d)', $team->name, $team->id);
            }

            $subscription->update_user_licenses_at = null;
            $subscription->save();

            $subscription->syncStartRenewalAt();
        }

        $message = sprintf('Updated quantity for %d subscriptions', $subscriptions->count());
        $messages[] = $message;
        $this->info($message);

        if ($subscriptions->count()) {
            $count = $query->count();
            $messages[] = sprintf('Still %d subscriptions left to update', $count);

            $to = config('mail.from.address');
            $messages = implode("\n\n", $messages);

            Mail::queue(new RawMailable($to, "Stripe user license quantities updated", $messages, $to));
        }

        $this->info('Finished');
    }
}
