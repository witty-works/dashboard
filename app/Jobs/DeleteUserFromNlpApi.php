<?php

namespace App\Jobs;

use App\Models\User;

class DeleteUserFromNlpApi extends AbstractDeleteFromNlpApi
{
    protected $email;

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
