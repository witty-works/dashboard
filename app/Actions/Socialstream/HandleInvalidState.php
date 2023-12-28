<?php

namespace App\Actions\Socialstream;

use Illuminate\Http\Response;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use Laravel\Socialite\Two\InvalidStateException;

class HandleInvalidState implements HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     *
     * @param  \Laravel\Socialite\Two\InvalidStateException  $exception
     * @param  callable  $callback
     * @return mixed
     */
    public function handle(InvalidStateException $exception): Response
    {
        throw $exception;
    }
}
