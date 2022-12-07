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

    public function deleteRules($url)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['url'])) {
            Log::debug("Endpoint URL not set, otherwise would delete: $url");

            return 0;
        }

        $endpoint['url'] .= $url;

        if (empty($endpoint['user'])) {
            $response = Http::delete($endpoint['url']);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->delete($endpoint['url']);
        }

        if ($response->failed() && $response->status() !== 404) {
            throw new RuntimeException("Unable to delete '{$url}.: " . $response->json('message'));
        }

        return 0;
    }
}
