<?php

namespace App\Listeners;

use App\Models\OrganizationGuidelines;
use App\Models\Team;
use Illuminate\Support\Facades\Http;
use Laravel\Jetstream\Events\TeamDeleted;

class UpdateOrganizationGuidelines
{
    public function handle($event)
    {
        $team = $event->team;

        if ($event instanceof TeamDeleted) {
            $this->deleteRules($team);
        } else {
            $this->updateRules($team);
        }
    }

    protected function deleteRules(Team $team)
    {
        $endpoint = config('app.organization_guidelines_endpoint');
        $data = [
            'id' => $team->id,
            'users' => $team->allUsers()->pluck('email')->toArray(),
        ];

        $endpoint['url'] .= "/delete_rules";

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

    protected function updateRules(Team $team)
    {
        $endpoint = config('app.organization_guidelines_endpoint');

        $termReplacements = $this->getTermReplacements($team);
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
            'id' => $team->id,
            'name' => $team->name,
            'plan' => $plan,
            'users' => $users,
            'false_positives' => $falsePositives,
            'term_replacements' => $termReplacements,
            'config' => $this->getConfig($team),
        ];

        $endpoint['url'] .= "/store_rules";

        if (empty($endpoint['user'])) {
            $response = Http::post($endpoint['url'], $data);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->post($endpoint['url'], $data);
        }

        if (config('app.debug') && $response->failed()) {
            dd($response->json(), $data);
        }
    }

    protected function getTermReplacements(Team $team)
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

    protected function getConfig(Team $team)
    {
        $organizationGuidelines = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);

        $config['store_context'] = [
            'value' => $team->store_context,
            'status' => 'force',
        ];

        $config['maximum_importance'] = [
            'value' => $organizationGuidelines->expert_mode ? 3 : 2,
            'status' => $organizationGuidelines->expert_mode_force ? 'force' : 'suggestion',
        ];

        $config['singular_they'] = [
            'value' => $organizationGuidelines->singular_they ? 'all_pronouns' : 'he_or_she',
            'status' => $organizationGuidelines->singular_they_force ? 'force' : 'suggestion',
        ];

        $config['show_inspiration_alternatives'] = [
            'value' => $organizationGuidelines->show_inspiration_alternatives,
            'status' => $organizationGuidelines->show_inspiration_alternatives_force ? 'force' : 'suggestion',
        ];

        $config['gendered_roles_format'] = [
            'value' => $organizationGuidelines->gendered_roles_format,
            'status' => $organizationGuidelines->gendered_roles_format_force ? 'force' : 'suggestion',
        ];

        $config['german_gender_ending'] = [
            'value' => $organizationGuidelines->german_gender_ending,
            'status' => $organizationGuidelines->german_gender_ending_force ? 'force' : 'suggestion',
        ];

        foreach (OrganizationGuidelines::DISABLED_CATEGORIES as $category) {
            $config[$category] = [
                'value' => !in_array($category, $organizationGuidelines->disabled_categories),
                'status' => in_array($category, $organizationGuidelines->disabled_categories_force) ? 'force' : 'suggestion',
            ];
        }

        $config['preferred_variants'] = [
            'value' => $organizationGuidelines->preferred_variants,
            'status' => $organizationGuidelines->preferred_variants_force ? 'force' : 'suggestion',
        ];

        return $config;
    }
}
