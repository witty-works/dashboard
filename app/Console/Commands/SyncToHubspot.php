<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncToHubspot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:sync {--batch-size=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users to Hubspot that do not yet have an ID stored in the database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!config('hubspot.enabled')) {
            $this->info('HubSpot is not enabled ...');

            return;
        }

        $hubspot = \HubSpot\Factory::createWithAccessToken(config('hubspot.access_token'));

        $this->info('Syncing to HubSpot ...');

        $batchSize = $this->option('batch-size') ?? false;
        $userCount = 0;

        foreach (User::whereNull('hubspot_id')->cursor() as $user) {
            $data = $user->getHubspotData();

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

                // @var CollectionResponseWithTotalSimplePublicObject $contactsPage
                $contactsPage = $hubspot->crm()->contacts()->searchApi()->doSearch($searchRequest);
                if ($contactsPage->getTotal()) {
                    $contactId = $contactsPage->getResults()[0]['id'];

                    $newProperties = new \HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput();
                    $newProperties->setProperties($data);

                    $hubspot->crm()->contacts()->basicApi()->update($contactId, $newProperties);
                } else {
                    $contactId = 0;
                }
            }

            $user->hubspot_id = $contactId;
            $user->save();

            $userCount++;

            if ($batchSize !== false) {
                $batchSize--;
                if ($batchSize <= 0) {
                    break;
                }
            }
        }

        $this->info("Finished syncing $userCount users");
    }
}
