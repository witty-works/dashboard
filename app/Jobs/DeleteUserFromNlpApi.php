<?php

namespace App\Jobs;

use App\Models\User;

class DeleteUserFromNlpApi extends AbstractDeleteFromNlpApi
{
    protected $email;

    /** Queued inside the deletion's transaction: send it only once that commits. */
    public $afterCommit = true;

    /** A user, or the address the NLP API knew them by before it changed. */
    public function __construct(User|string $user, $queue = 'low')
    {
        $this->email = $user instanceof User ? $user->email : $user;
        $this->onQueue($queue);
    }

    public function handle()
    {
        $url = '/user/configs?' . http_build_query(['email' => $this->email]);
        return $this->deleteRules($url);
    }
}
