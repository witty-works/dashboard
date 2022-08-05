<?php

namespace App\Listeners;

use App\Models\GuidelinesInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AbstractUpdateGuidelines
{
    public function deleteRules($url)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['url'])) {
            return;
        }

        $endpoint['url'] .= $url;

        if (empty($endpoint['user'])) {
            $response = Http::delete($endpoint['url']);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->delete($endpoint['url']);
        }

        if (config('app.debug') && $response->failed()) {
            dd($response->body(), $url);
        }
    }

    public function updateRules($url, $data)
    {
        $endpoint = config('app.nlp_api_endpoint');
        if (empty($endpoint['url'])) {
            return;
        }

        $endpoint['url'] .= $url;

        if (empty($endpoint['user'])) {
            $response = Http::post($endpoint['url'], $data);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->post($endpoint['url'], $data);
        }

        if ($response->failed()) {
            if (config('app.debug')) {
                dd($response->json(), $data);
            } else {
                throw new RuntimeException($response->body());
            }
        }
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
        if ($type === 'allow_witty_works') {
            $type = 'allow';
        }

        $data = [
            'domains' => [],
        ];

        if ($type === 'allow_witty_works') {
            $type = 'allow';
            $data['domains'][] = 'witty.works';
        } else {
            foreach ($domains as $domain) {
                $data['domains'][] = $domain->domain;
            }
        }

        $data['type'] = $type;

        return $data;
    }

    protected function getConfig($guidelines)
    {
        $config['maximum_importance'] = [
            'value' => $guidelines->expert_mode ? 3 : 2,
            'status' => $guidelines->expert_mode_force === false ? 'suggestion' : 'force',
        ];

        $config['singular_they'] = [
            'value' => $guidelines->singular_they ? 'all_pronouns' : 'he_or_she',
            'status' => $guidelines->english_rules_force === false ? 'suggestion' : 'force',
        ];

        $config['show_inspiration_alternatives'] = [
            'value' => (bool) $guidelines->show_inspiration_alternatives,
            'status' => $guidelines->show_inspiration_alternatives_force === false ? 'suggestion' : 'force',
        ];

        $config['gendered_roles_format'] = [
            'value' => $guidelines->gendered_roles_format,
            'status' => $guidelines->german_rules_force === false ? 'suggestion' : 'force',
        ];

        $config['german_gender_ending'] = [
            'value' => $guidelines->german_gender_ending,
            'status' => $guidelines->german_rules_force === false ? 'suggestion' : 'force',
        ];

        foreach (GuidelinesInterface::DISABLED_CATEGORIES as $category) {
            $config[$category] = [
                'value' => !in_array($category, $guidelines->disabled_categories),
                'status' => null === $guidelines->disabled_categories_force
                    || in_array($category, $guidelines->disabled_categories_force)
                    ? 'force' : 'suggestion',
            ];
        }

        $config['preferred_variants'] = [
            'value' => $guidelines->preferred_variants,
            'status' => $guidelines->preferred_variants_force === false ? 'suggestion' : 'force',
        ];

        return $config;
    }
}
