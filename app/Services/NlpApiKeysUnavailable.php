<?php

namespace App\Services;

use RuntimeException;

/**
 * The NLP API could not create, list, replace or revoke a key. The message
 * names the request and the status, never the response body, which may hold
 * a key.
 */
class NlpApiKeysUnavailable extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $status = null)
    {
        parent::__construct($message);
    }
}
