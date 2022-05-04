<?php

namespace App\Listeners;

use App\Models\OrganizationGuidelines;
use App\Models\Team;
use Illuminate\Support\Facades\Http;
use Laravel\Jetstream\Events\TeamDeleted;
use RuntimeException;

class UpdateOrganizationGuidelines
{
    public function handle($event)
    {
        $team = $event->team;

        if ($event instanceof TeamDeleted) {
            self::deleteRules($team);
        } else {
            self::updateRules($team);
        }
    }

    static public function deleteRules(Team $team)
    {
        $endpoint = config('app.organization_guidelines_endpoint');
        $data = [
            'id' => $team->id,
            'users' => $team->allUsers()->pluck('email')->toArray(),
        ];

        $endpoint['url'] .= '/delete_rules';

        if (empty($endpoint['user'])) {
            $response = Http::delete($endpoint['url'], $data);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->delete($endpoint['url'], $data);
        }

        if (config('app.debug') && $response->failed()) {
            dd($response->body(), $data);
        }
    }

    static public function updateRules(Team $team)
    {
        $endpoint = config('app.organization_guidelines_endpoint');

        $termReplacements = self::getTermReplacements($team);
        $falsePositives = $team->falsePositives()->pluck('false_positive')->toArray();

        if ($team->subscribed()) {
            $users = $team->allUsers()->pluck('email')->toArray();
        } else {
            $users = [$team->owner->email];
            $team->store_context = true;
            $falsePositives = array_slice($falsePositives, 0, $team->false_positive_count);
            $termReplacements = array_slice($termReplacements, 0, $team->getTermReplacementsCount());
        }

        $plan = $team->planId();

        $data = [
            'id' => $team->posthogId(),
            'name' => $team->name,
            'plan' => $plan,
            'users' => $users,
            'false_positives' => $falsePositives,
            'term_replacements' => $termReplacements,
            'config' => self::getConfig($team),
        ];

        $endpoint['url'] .= '/store_rules';

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

    static protected function getTermReplacements(Team $team)
    {
        $termReplacements = [];
        foreach ($team->termReplacements as $termReplacement) {
            $termReplacementData = [
                'term' => $termReplacement->term,
                'alternatives' => [$termReplacement->replacement],
            ];

            if ($termReplacement->explanation !== null) {
                $termReplacementData['explanation'] = [
                    'text' => $termReplacement->explanation,
                    'url' => $termReplacement->url,
                    'icon' => $termReplacement->emoji,
                ];
            }

            $termReplacements[] = $termReplacementData;
        }

        return $termReplacements;
    }

    static protected function getConfig(Team $team)
    {
        $orgGuidelines = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);

        $config['store_context'] = [
            'value' => $team->store_context,
            'status' => 'force',
        ];

        $config['maximum_importance'] = [
            'value' => $orgGuidelines->expert_mode ? 3 : 2,
            'status' => $orgGuidelines->expert_mode_force ? 'force' : 'suggestion',
        ];

        $config['singular_they'] = [
            'value' => $orgGuidelines->singular_they ? 'all_pronouns' : 'he_or_she',
            'status' => $orgGuidelines->singular_they_force ? 'force' : 'suggestion',
        ];

        $config['show_inspiration_alternatives'] = [
            'value' => $orgGuidelines->show_inspiration_alternatives,
            'status' => $orgGuidelines->show_inspiration_alternatives_force ? 'force' : 'suggestion',
        ];

        $config['gendered_roles_format'] = [
            'value' => $orgGuidelines->gendered_roles_format,
            'status' => $orgGuidelines->gendered_roles_format_force ? 'force' : 'suggestion',
        ];

        $config['german_gender_ending'] = [
            'value' => $orgGuidelines->german_gender_ending,
            'status' => $orgGuidelines->german_gender_ending_force ? 'force' : 'suggestion',
        ];

        foreach (OrganizationGuidelines::DISABLED_CATEGORIES as $category) {
            $config[$category] = [
                'value' => !in_array($category, $orgGuidelines->disabled_categories),
                'status' => in_array($category, $orgGuidelines->disabled_categories_force) ? 'force' : 'suggestion',
            ];
        }

        $config['preferred_variants'] = [
            'value' => $orgGuidelines->preferred_variants,
            'status' => $orgGuidelines->preferred_variants_force ? 'force' : 'suggestion',
        ];

        return $config;
    }
}
