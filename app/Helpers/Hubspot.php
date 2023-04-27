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
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

// https://henrywang.nl/hubspot-contact-properties-list/
class Hubspot
{
    protected $api;

    public function __construct()
    {
        $this->api = Factory::createWithAccessToken(config('hubspot.access_token'));
    }

    public function createContactViaForm(User $user)
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
                'hutk' => $user->hubspotutk,
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

    public function updateContact(User $user)
    {
        if (empty($user->hubspot_id)) {
            return false;
        }

        $data = $user->getHubspotData(true);
        $newProperties = new ContactsSimplePublicObjectInput();
        $newProperties->setProperties($data);

        $this->api->crm()->contacts()->basicApi()->update($user->hubspot_id, $newProperties);

        return true;
    }

    public function updateUserFromContact(User $user, $contact, $updateHubspot = false)
    {
        if (!empty($contact['properties']['hs_additional_emails'])) {
            $additionalEmails = explode(';', $contact['properties']['hs_additional_emails']);
            $additionalEmails[] = $contact['properties']['email'];
            $users = User::whereIn('email', $additionalEmails)->where('email', '!=', $user->email)->get();

            // this contact's emails are associated to multiple user accounts
            if (count($users) > 0) {
                // send notification
                if ($user->hubspot_id !== 0) {
                    Mail::send([], [], function (Message $message) use ($users, $contact) {
                        $html = "<a href=\"https://app-eu1.hubspot.com/contacts/24904016/contact/{$contact['id']}\">Contact</a>";
                        $html .= ' is associated with emails for different user accounts<br>';
                        $html .= 'Problematic emails: ' . implode(', ', $users->pluck('email')->toArray());

                        $message->to('support@witty.works')
                            ->subject('Overlapping emails in HubSpot contacts')
                            ->from('support@witty.works')
                            ->html($html);
                    });
                }

                unset($contact['id']);
                $updateHubspot = false;
            }
        }

        if (empty($contact['id'])) {
            $user->hubspot_id = 0;
        } else {
            $user->hubspot_id = $contact['id'];
        }

        if (empty($contact['properties']['hs_analytics_source'])) {
            $user->hubspot_source = null;
        } else {
            $user->hubspot_source = $contact['properties']['hs_analytics_source'];
        }

        if (empty($contact['properties']['associatedcompanyid'])) {
            $user->hubspot_company_id = null;
        } else {
            $user->hubspot_company_id = $contact['properties']['associatedcompanyid'];
        }

        if (empty($contact['properties']['sales_readiness'])) {
            $user->hubspot_sales_readiness = null;
        } else {
            $user->hubspot_sales_readiness = $contact['properties']['sales_readiness'];
        }

        $user->saveQuietly();

        $result = $user->wasChanged();

        if ($updateHubspot) {
            $this->updateContact($user);
        }

        return $result;
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

        $fetchProperties = [
            'hs_analytics_source',
            'associatedcompanyid',
            'sales_readiness',
            'email',
            'hs_additional_emails',
        ];

        $searchRequest->setProperties($fetchProperties);

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

        $fetchProperties = [
            'name',
        ];

        $searchRequest->setProperties($fetchProperties);

        // @var CollectionResponseWithTotalSimplePublicObject $results
        $results = $this->api->crm()->companies()->searchApi()->doSearch($searchRequest);
        if ($results->getTotal() !== 1) {
            return null;
        }

        return $results->getResults()[0];
    }

    public function exportTable($tableName, $draft = false)
    {
        $format = 'CSV';

        if ($draft) {
            return $this->api->cms()->hubdb()->tablesApi()->exportDraftTable($tableName, $format);
        }

        return $this->api->cms()->hubdb()->tablesApi()->exportTable($tableName, $format);
    }

    public function publishTable($tableName)
    {
        $this->api->cms()->hubdb()->tablesApi()->publishDraftTable($tableName);

        return true;
    }
}
