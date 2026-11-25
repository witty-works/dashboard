<?php

namespace App\Jobs;

use App\Models\User;

class DeleteUserFromNlpApi extends AbstractDeleteFromNlpApi
{
    protected $email;

    /** Queued inside the deletion's transaction: send it only once that commits. */
    public $afterCommit = true;

    public function __construct(User $user, $queue = 'low')
    {
        $this->email = $user->email;
        $this->onQueue($queue);
    }

    public function handle()
    {
        $url = '/user/configs?' . http_build_query(['email' => $this->email]);
        return $this->deleteRules($url);
    }
}
