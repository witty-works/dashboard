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

abstract class AbstractSyncToNlpApi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 50;

    public function updateRules($url, $data)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['urls']) || empty($endpoint['sync_rules'])) {
            Log::debug("Endpoint URL not set, otherwise would update: $url ({$data['id']})");

            return true;
        }

        $data['sync_date'] = now()->toDateTimeString();

        // DASHBOARD-N8 - Input should be a valid dictionary
        if (empty($data['term_replacements'])) {
            unset($data['term_replacements']);
        }

        foreach ($endpoint['urls'] as $baseUrl) {
            if (empty($baseUrl)) {
                continue;
            }

            $this->postData($data, $baseUrl . $url, $endpoint['user'], $endpoint['password']);
        }

        return $data;
    }

    protected function postData($data, $url, $user, $password)
    {
        if (empty($user)) {
            $response = Http::post($url, $data);
        } else {
            $response = Http::withBasicAuth($user, $password)
                ->post($url, $data);
        }

        if ($response->failed() && $response->status() !== 404) {
            throw new RuntimeException("Unable to write to '{$url} ({$data['id']}): " . $response->body());
        }

        return $response;
    }

    protected function getFalsePositives($falsePositives, $subscribed, $count)
    {
        $data = $falsePositives->pluck('false_positive')->toArray();

        if (!$subscribed) {
            $data = array_slice($data, 0, $count);
        }

        return $data;
    }

    protected function getTermReplacements($termReplacements, $subscribed, $count)
    {
        $data = [];
        foreach ($termReplacements as $termReplacement) {
            $termReplacementData = [
                'alternatives' => [$termReplacement->replacement],
                'word_type' => $termReplacement->word_type,
                'term' => $termReplacement->term,
            ];

            if ($termReplacement->explanation !== null) {
                $termReplacementData['explanation'] = [
                    'text' => $termReplacement->explanation,
                    'url' => $termReplacement->url,
                    'icon' => $termReplacement->emoji,
                ];
            }

            if ($termReplacement->language_code) {
                $languageCodes = [$termReplacement->language_code];
            } else {
                $languageCodes = ['en', 'de', 'fr'];
            }

            foreach ($languageCodes as $languageCode) {
                $data[$termReplacement->term . '|' . $languageCode] = $termReplacementData;
            }
        }

        if (!$subscribed) {
            $data = array_slice($data, 0, $count);
        }

        return $data;
    }

    protected function getDomains($domains, $type)
    {
        $data = [
            'list' => [],
        ];

        if ($type === 'allow_witty_works') {
            $type = 'allow';
            $data['list'][] = 'witty.works';
        } else {
            foreach ($domains as $domain) {
                $data['list'][] = $domain->domain;
            }
        }

        $data['type'] = $type;

        return $data;
    }

    protected function getConfig($guidelines, $forceDefault)
    {
        $guidelines->show_inspiration_alternatives_force
            = $guidelines->show_inspiration_alternatives_force ?? $forceDefault;

        $config['show_inspiration_alternatives'] = [
            'value' => (bool) $guidelines->show_inspiration_alternatives,
            'status' => $guidelines->show_inspiration_alternatives_force ? 'force' : 'suggestion',
        ];

        $guidelines->generic_masculine_force = $guidelines->generic_masculine_force ?? $forceDefault;

        $config['gendered_roles_format'] = [
            'value' => $guidelines->gendered_roles_format,
            'status' => $guidelines->generic_masculine_force ? 'force' : 'suggestion',
        ];

        $guidelines->german_rules_force = $guidelines->german_rules_force ?? $forceDefault;

        $config['german_gender_ending'] = [
            'value' => $guidelines->german_gender_ending,
            'status' => $guidelines->german_rules_force ? 'force' : 'suggestion',
        ];

        $guidelines->preferred_variants_force = $guidelines->preferred_variants_force ?? $forceDefault;

        $config['preferred_variants'] = [
            'value' => $guidelines->preferred_variants,
            'status' => $guidelines->preferred_variants_force ? 'force' : 'suggestion',
        ];

        return $config;
    }
}
