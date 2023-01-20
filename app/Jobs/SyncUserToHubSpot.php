<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use HubSpot\Factory as HubSpotFactory;
use HubSpot\Client\Crm\Contacts\Model\Filter as HubSpotFilter;
use HubSpot\Client\Crm\Contacts\Model\FilterGroup as HubSpotFilterGroup;
use HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest as HubSpotPublicObjectSearchRequest;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput as HubSpotSimplePublicObjectInput;
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
    protected $cookie;
    protected $hubspot;

    public function __construct(User $user, $cookie = null)
    {
        $this->id = $user->id;
        $this->cookie = $cookie;
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

        $this->hubspot = HubSpotFactory::createWithAccessToken(config('hubspot.access_token'));

        if ($this->cookie) {
            $this->createContactViaForm($user);
        } else {
            $data = $user->getHubspotData(true);

            $hubSpotData = $this->syncContact($user, $data);
            if (empty($hubSpotData)) {
                $hubSpotData = $this->createContact($user, $data);
            }

            if (!empty($hubSpotData)) {
                $this->updateUser($user, $hubSpotData);
            }
        }

        return $hubSpotData;
    }

    protected function createContactViaForm(User $user)
    {
        $names = explode(' ', $user->name);
        $lastname = array_pop($names);
        $firstname = implode(' ', $names);

        $data = [
            'fields' => [
                [
                    'name' => 'email',
                    'value' => $user->email,
                ],
                [
                    'name' => 'firstname',
                    'value' => $firstname,
                ],
                [
                    'name' => 'lastname',
                    'value' => $lastname,
                ],
            ],
            'context' => [
                'hutk' => $this->cookie,
            ],
        ];

        $hubspotPortalId = config('hubspot.hub_id');
        $hubspotFormGuid = config('hubspot.form_id');

        return $this->hubspot->apiRequest([
            'method' => 'POST',
            'baseUrl' => 'https://api.hsforms.com',
            'path' => "/submissions/v3/integration/submit/$hubspotPortalId/$hubspotFormGuid",
            'body' => $data,
        ]);
    }

    protected function createContact(User $user, array $data)
    {
        $current = Carbon::now();

        # if the user was created under X minutes ago, do not force the creation of the contact
        if ($current->diffInMinutes($user->created_at) < config('hubspot.force_create_after')) {
            return;
        }

        try {
            $data['email'] = $user->email;

            $contactInput = new HubSpotSimplePublicObjectInput();
            $contactInput->setProperties($data);

            $result = $this->hubspot->crm()->contacts()->basicApi()->create($contactInput);

            return ['id' => $result['id'], 'hubspot_source' => 'OFFLINE'];
        } catch (\HubSpot\Client\Crm\Contacts\ApiException $e) {
        }
    }

    protected function syncContact(User $user, $data)
    {
        $filter = new HubSpotFilter();
        $filter
            ->setOperator('EQ')
            ->setPropertyName('email')
            ->setValue($user->email);

        $filterGroup = new HubSpotFilterGroup();
        $filterGroup->setFilters([$filter]);

        $searchRequest = new HubSpotPublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$filterGroup]);

        $searchRequest->setProperties(['hs_analytics_source']);

        // @var CollectionResponseWithTotalSimplePublicObject $contactsPage
        $contactsPage = $this->hubspot->crm()->contacts()->searchApi()->doSearch($searchRequest);
        if (!$contactsPage->getTotal()) {
            return;
        }

        $hubSpotData = [
            'id' => $contactsPage->getResults()[0]['id'],
        ];
        if (!empty($contactsPage->getResults()[0]['properties']['hs_analytics_source'])) {
            $hubSpotData['hubspot_source'] = $contactsPage->getResults()[0]['properties']['hs_analytics_source'];
        }

        $newProperties = new HubSpotSimplePublicObjectInput();
        $newProperties->setProperties($data);

        $this->hubspot->crm()->contacts()->basicApi()->update($hubSpotData['id'], $newProperties);

        return $hubSpotData;
    }

    protected function updateUser(User $user, array $data)
    {
        $user->hubspot_id = $data['id'];
        if (
            !empty($data['hubspot_source'])
            && $user->hubspot_source !== $data['hubspot_source']
        ) {
            $user->hubspot_source = $data['hubspot_source'];
        }

        $user->saveQuietly();

        if ($user->wasChanged()) {
            dispatch(new SyncUserToPosthog($user));
        }
    }
}
