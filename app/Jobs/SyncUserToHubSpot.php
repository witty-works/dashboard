<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;

class SyncUserToHubSpot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    public function __construct(User $user)
    {
        $this->id = $user->id;
    }

    public function handle()
    {
        $user = User::find($this->id);
        if (!$user instanceof User) {
            throw new InvalidArgumentException("User id '{$this->id} does not exist.");
        }

        if (!config('hubspot.enabled')) {
            Log::debug("Hubspot not enabled, otherwise update user: {$user->name} ({$user->id})");

            return 0;
        }

        $hubspot = \HubSpot\Factory::createWithAccessToken(config('hubspot.access_token'));
        $data = $user->getHubspotData(true);

        try {
            $newData = $data;
            $newData['email'] = $user->email;

            $contactInput = new \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput();
            $contactInput->setProperties($newData);

            $contact = $hubspot->crm()->contacts()->basicApi()->create($contactInput);
            $contactId = $contact['id'];
        } catch (\HubSpot\Client\Crm\Contacts\ApiException $e) {
            $filter = new \HubSpot\Client\Crm\Contacts\Model\Filter();
            $filter
                ->setOperator('EQ')
                ->setPropertyName('email')
                ->setValue($user->email);

            $filterGroup = new \HubSpot\Client\Crm\Contacts\Model\FilterGroup();
            $filterGroup->setFilters([$filter]);

            $searchRequest = new \HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest();
            $searchRequest->setFilterGroups([$filterGroup]);

            $searchRequest->setProperties(['hs_analytics_source']);

            // @var CollectionResponseWithTotalSimplePublicObject $contactsPage
            $contactsPage = $hubspot->crm()->contacts()->searchApi()->doSearch($searchRequest);
            if ($contactsPage->getTotal()) {
                $contactId = $contactsPage->getResults()[0]['id'];
                if (!empty($contactsPage->getResults()[0]['properties']['hs_analytics_source'])) {
                    $contactSource = $contactsPage->getResults()[0]['properties']['hs_analytics_source'];
                }

                $newProperties = new \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput();
                $newProperties->setProperties($data);

                $hubspot->crm()->contacts()->basicApi()->update($contactId, $newProperties);
            }
        }

        $user->hubspot_id = $contactId;
        if (!empty($contactSource) && $user->hubspot_source != $contactSource) {
            $user->hubspot_source = $contactSource;
            $contactSourceUpdated = true;
        }

        $user->saveQuietly();

        if (!empty($contactSourceUpdated)) {
            dispatch(new SyncUserToPosthog($user));
        }

        return 0;
    }
}
