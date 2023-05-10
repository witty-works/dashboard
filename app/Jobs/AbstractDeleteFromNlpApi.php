<?php

namespace App\Jobs;

use RuntimeException;
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

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 50;

    public function deleteRules($url)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['urls'])) {
            Log::debug("Endpoint URL not set, otherwise would delete: $url");

            return true;
        }

        foreach ($endpoint['urls'] as $baseUrl) {
            if (empty($baseUrl)) {
                continue;
            }

            $this->deleteData($baseUrl . $url, $endpoint['user'], $endpoint['password']);
        }

        return true;
    }

    protected function deleteData($url, $user, $password)
    {
        if (empty($user)) {
            $response = Http::delete($url);
        } else {
            $response = Http::withBasicAuth($user, $password)
                ->delete($url);
        }

        if ($response->failed() && $response->status() !== 404) {
            throw new RuntimeException("Unable to delete '{$url}.: " . $response->json('message'));
        }

        return $response;
    }
}
