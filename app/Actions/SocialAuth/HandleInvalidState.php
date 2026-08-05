<?php

namespace App\Actions\SocialAuth;

use Illuminate\Http\Response;
use App\Contracts\SocialAuth\HandlesInvalidState;
use Laravel\Socialite\Two\InvalidStateException;

class HandleInvalidState implements HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     */
    public function handle(InvalidStateException $exception): Response
    {
        throw $exception;
    }
}
