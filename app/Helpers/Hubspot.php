<?php

namespace App\Helpers;

use App\Models\User;
use HubSpot\Factory;
use HubSpot\Client\Crm\Contacts\Model\Filter as ContactsFilter;
use HubSpot\Client\Crm\Contacts\Model\FilterGroup as ContactsFilterGroup;
use HubSpot\Client\Crm\Contacts\Model\PublicObjectSearchRequest as ContactsPublicObjectSearchRequest;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput as ContactsSimplePublicObjectInput;
use HubSpot\Client\Crm\Companies\Model\Filter as CompaniesFilter;
use HubSpot\Client\Crm\Companies\Model\FilterGroup as CompaniesFilterGroup;
use HubSpot\Client\Crm\Companies\Model\PublicObjectSearchRequest as CompaniesPublicObjectSearchRequest;

// https://henrywang.nl/hubspot-contact-properties-list/
class Hubspot
{
    protected $api;

    public function __construct()
    {
        $this->api = Factory::createWithAccessToken(config('hubspot.access_token'));
    }

    public function createContactViaForm(User $user, $hubspotutk)
    {
        $data = [
            'fields' => [
                [
                    'name' => 'email',
                    'value' => $user->email,
                ],
                [
                    'name' => 'firstname',
                    'value' => $user->first_name,
                ],
                [
                    'name' => 'lastname',
                    'value' => $user->last_name,
                ],
            ],
            'context' => [
                'hutk' => $hubspotutk,
            ],
        ];

        $hubspotPortalId = config('hubspot.hub_id');
        $hubspotFormGuid = config('hubspot.form_id');

        return $this->api->apiRequest([
            'method' => 'POST',
            'baseUrl' => 'https://api.hsforms.com',
            'path' => "/submissions/v3/integration/submit/$hubspotPortalId/$hubspotFormGuid",
            'body' => $data,
        ]);
    }

    public function createContact(User $user)
    {
        try {
            $data = $user->getHubspotData(true);
            $data['email'] = $user->email;
            $data['firstname'] = $user->first_name;
            $data['lastname'] = $user->last_name;

            $contactInput = new ContactsSimplePublicObjectInput();
            $contactInput->setProperties($data);

            $result = $this->api->crm()->contacts()->basicApi()->create($contactInput);
        } catch (\HubSpot\Client\Crm\Contacts\ApiException $e) {
            return [];
        }

        return ['id' => $result['id'], 'hubspot_source' => 'OFFLINE'];
    }

    public function updateContact($contactId, User $user)
    {
        $data = $user->getHubspotData(true);
        $newProperties = new ContactsSimplePublicObjectInput();
        $newProperties->setProperties($data);

        $this->api->crm()->contacts()->basicApi()->update($contactId, $newProperties);

        return true;
    }

    public function updateUserFromContact(User $user, $contact)
    {
        $user->hubspot_id = $contact['id'] ?? null;
        $user->hubspot_source = $contact['properties']['hs_analytics_source'] ?? null;
        $user->hubspot_company_id = $contact['properties']['associatedcompanyid'] ?? null;

        $user->saveQuietly();

        return $user->wasChanged();
    }


    public function findContact(User $user)
    {
        $properties = ['email', 'hs_additional_emails'];
        $filterGroups = [];
        foreach ($properties as $property) {
            $filter = new ContactsFilter();
            $filter
                ->setOperator('EQ')
                ->setPropertyName($property)
                ->setValue($user->email);

            $filterGroup = new ContactsFilterGroup();
            $filterGroup->setFilters([$filter]);
            $filterGroups[] = $filterGroup;
        }

        $searchRequest = new ContactsPublicObjectSearchRequest();
        $searchRequest->setFilterGroups($filterGroups);

        $searchRequest->setProperties(['hs_analytics_source', 'associatedcompanyid']);

        // @var CollectionResponseWithTotalSimplePublicObject $results
        $results = $this->api->crm()->contacts()->searchApi()->doSearch($searchRequest);
        if ($results->getTotal() !== 1) {
            return null;
        }

        return $results->getResults()[0];
    }

    public function findCompanyByUser(User $user)
    {
        $email = explode('@', $user->email);
        if (empty($email[1])) {
            return null;
        }

        $filter = new CompaniesFilter();
        $filter
            ->setOperator('EQ')
            ->setPropertyName('domain')
            ->setValue($email[1]);

        $filterGroup = new CompaniesFilterGroup();
        $filterGroup->setFilters([$filter]);

        $searchRequest = new CompaniesPublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$filterGroup]);

        // @var CollectionResponseWithTotalSimplePublicObject $results
        $results = $this->api->crm()->companies()->searchApi()->doSearch($searchRequest);
        if ($results->getTotal() !== 1) {
            return null;
        }

        return $results->getResults()[0];
    }
}
