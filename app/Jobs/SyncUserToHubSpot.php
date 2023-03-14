<?php

namespace App\Jobs;

use App\Helpers\Hubspot;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Spatie\RateLimitedMiddleware\RateLimited;

class SyncUserToHubSpot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $hubspot;

    public function __construct(User $user)
    {
        $this->id = $user->id;
    }

    public function retryUntil()
    {
        return now()->addHour(2);
    }

    public function middleware()
    {
        $rateLimitedMiddleware = new RateLimited(false);

        $rateLimitedMiddleware
            ->allow(config('hubspot.rate.limit'))
            ->everySeconds(config('hubspot.rate.interval_seconds'))
            ->releaseAfterBackoff($this->attempts(), config('hubspot.rate.muiltiplier'));

        return [$rateLimitedMiddleware];
    }

    public function handle()
    {
        $user = User::find($this->id);
        if (!$user instanceof User) {
            throw new InvalidArgumentException("User id '{$this->id} does not exist.");
        }

        if (!config('hubspot.enabled')) {
            Log::debug("Hubspot not enabled, otherwise update user: {$user->name} ({$user->id})");

            return true;
        }

        $this->hubspot = new Hubspot();

        $contact = $this->hubspot->findContact($user);
        if ($contact) {
            $oldHubspotCompanyId = $user->hubspot_company_id;
            $changed = $this->hubspot->updateUserFromContact($user, $contact);
            $this->hubspot->updateContact($contact['id'], $user);

            // company ID changed, so we may need to update other users in HubSpot
            if (false && $oldHubspotCompanyId !== $user->hubspot_company_id) {
                $ids = [];

                // contact added to a new company
                if ($user->hubspot_company_id) {
                    $ids[] = $user->hubspot_company_id;
                }

                // contact moved to another company
                if ($oldHubspotCompanyId) {
                    $ids[] = $oldHubspotCompanyId;
                }

                $query = User::whereIn('hubspot_company_id', $ids)
                    ->whereNot('id', $user->id);

                foreach ($query->get() as $otherUser) {
                    dispatch(new SyncUserToHubSpot($otherUser));
                }
            }
        } else {
            if (empty($user->hubspotutk)) {
                $contact = $this->hubspot->createContact($user);
            } else {
                // create via hubspot 'hubspotutk' cookie
                $this->hubspot->createContactViaForm($user, $user->hubspotutk);
            }

            if (empty($contact)) {
                return true;
            }

            $changed = $this->hubspot->updateUserFromContact($user, $contact);
        }

        if ($changed) {
            dispatch(new SyncToPosthog($user));
        }

        User::withoutTimestamps(function () use ($user) {
            $user->hubspot_last_sync = now();
            $user->saveQuietly();
        });

        return $user->getHubspotData(true);
    }
}
