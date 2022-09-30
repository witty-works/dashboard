<?php

namespace App\Jobs;

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

    public function updateRules($url, $data)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['url'])) {
            Log::debug("Endpoint URL not set, otherwise would update: $url ({$data['id']})");

            return 0;
        }

        $endpoint['url'] .= $url;

        if (empty($endpoint['user'])) {
            $response = Http::post($endpoint['url'], $data);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->post($endpoint['url'], $data);
        }

        if ($response->failed() && $response->status() !== 404) {
            return -1;
        }

        return 0;
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
            ];

            if ($termReplacement->explanation !== null) {
                $termReplacementData['explanation'] = [
                    'text' => $termReplacement->explanation,
                    'url' => $termReplacement->url,
                    'icon' => $termReplacement->emoji,
                ];
            }

            $data[$termReplacement->term] = $termReplacementData;
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

    protected function getConfig($guidelines)
    {
        $config['maximum_importance'] = [
            'value' => $guidelines->expert_mode ? 3 : 2,
            'status' => $guidelines->expert_mode_force ? 'force' : 'suggestion',
        ];

        $config['singular_they'] = [
            'value' => $guidelines->singular_they ? 'all_pronouns' : 'he_or_she',
            'status' => $guidelines->english_rules_force === false ? 'suggestion' : 'force',
        ];

        $config['show_inspiration_alternatives'] = [
            'value' => (bool) $guidelines->show_inspiration_alternatives,
            'status' => $guidelines->show_inspiration_alternatives_force ? 'force' : 'suggestion',
        ];

        $config['gendered_roles_format'] = [
            'value' => $guidelines->gendered_roles_format,
            'status' => $guidelines->german_rules_force ? 'force' : 'suggestion',
        ];

        $config['german_gender_ending'] = [
            'value' => $guidelines->german_gender_ending,
            'status' => $guidelines->german_rules_force ? 'force' : 'suggestion',
        ];

        $config['preferred_variants'] = [
            'value' => $guidelines->preferred_variants,
            'status' => $guidelines->preferred_variants_force ? 'force' : 'suggestion',
        ];

        return $config;
    }
}
