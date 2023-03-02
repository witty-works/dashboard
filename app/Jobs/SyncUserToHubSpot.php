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
            ->everySeconds(config('hubspot.rate.interval_seconds'));

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

        $data = $user->getHubspotData(true);

        $contact = $this->hubspot->syncContact($user, $data);
        if (empty($contact)) {
            if (!empty($user->hubspotutk)) {
                // create via hubspot 'hubspotutk' cookie
                $this->hubspot->createContactViaForm($user, $user->hubspotutk);

                return true;
            }

            $contact = $this->hubspot->createContact($user, $data);
        }

        if (empty($contact)) {
            return true;
        }

        $hubSpotData = $this->hubspot->getDataFromContact($contact);

        $user->hubspot_id = $hubSpotData['id'];

        if (
            !empty($hubSpotData['hubspot_source'])
            && $user->hubspot_source !== $hubSpotData['hubspot_source']
        ) {
            $user->hubspot_source = $hubSpotData['hubspot_source'];
        }

        $user->saveQuietly();

        if ($user->wasChanged()) {
            dispatch(new SyncToPosthog($user));
        }

        return $hubSpotData;
    }
}
