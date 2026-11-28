<?php

namespace App\Jobs;

use App\Jobs\Concerns\RetriesThroughOutages;
use RuntimeException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class AbstractDeleteFromNlpApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use RetriesThroughOutages;

    public function deleteRules($url)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['urls'])) {
            Log::debug('Endpoint URL not set, otherwise would delete: ' . strtok($url, '?'));

            return true;
        }

        foreach ($endpoint['urls'] as $baseUrl) {
            if (empty($baseUrl)) {
                continue;
            }

            $this->deleteData(rtrim($baseUrl, '/') . $url, $endpoint['user'], $endpoint['password']);
        }

        return true;
    }

    protected function deleteData($url, $user, $password)
    {
        try {
            if (empty($user)) {
                $response = Http::delete($url);
            } else {
                $response = Http::withBasicAuth($user, $password)
                    ->delete($url);
            }
        } catch (ConnectionException) {
            // Guzzle's message ends with the URL, query string included.
            throw new RuntimeException("The NLP API could not be reached to delete '" . strtok($url, '?') . "'");
        }

        // The path and the status only: the query string names the person,
        // and the answer may repeat it. The message reaches Sentry and the
        // failed-job mail. A 404 too: the API answers a delete of something
        // already gone with 204, so it means a wrong address.
        if ($response->failed()) {
            throw new RuntimeException("Unable to delete '" . strtok($url, '?') . "': HTTP {$response->status()}");
        }

        return $response;
    }
}
