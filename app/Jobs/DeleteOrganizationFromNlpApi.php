<?php

namespace App\Jobs;

use App\Models\Team;

class DeleteOrganizationFromNlpApi extends AbstractDeleteFromNlpApi
{
    /** Queued inside a deletion's transaction: send it only once that commits. */
    public $afterCommit = true;

    protected $posthogId;

    public function __construct(Team $team, $queue = 'low')
    {
        $this->posthogId = $team->posthogId();
        $this->onQueue($queue);
    }

    public function handle()
    {
        $url = '/organization/configs?' . http_build_query(['organization_id' => $this->posthogId]);
        return $this->deleteRules($url);
    }
}
