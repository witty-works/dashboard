<?php

namespace App\Listeners;

use App\Models\OrganizationGuidelines;
use Illuminate\Support\Facades\Http;

class UpdateOrganizationGuidelines
{
    public function handle($event)
    {
        $team = $event->team;

        $termReplacements = [];
        foreach ($team->termReplacements as $termReplacement) {
            $termReplacements[] = [
                'term' => $termReplacement->term,
                'alternatives' => [$termReplacement->replacement]
            ];
        }

        $falsePositives = $team->falsePositives()->pluck('false_positive')->toArray();

        if ($team->subscribed()) {
            $users = $team->allUsers()->pluck('email')->toArray();
        } else {
            $users = [$team->owner->email];
            $team->store_context = true;
            $falsePositives = array_slice($falsePositives, 0, $team->false_positive_count);
            $termReplacements = array_slice($termReplacements, 0, $team->term_replacement_count);
        }

        $plan = $team->planId();

        $organizationGuidelines = OrganizationGuidelines::firstOrNew(['team_id' => $team->id]);
        $suggestion = [];
        $force = [];

        $suggestion['maximum_importance'] = $organizationGuidelines->expert_mode ? 3 : 2;
        if ($organizationGuidelines->expert_mode_force) {
            $force['maximum_importance'] = $suggestion['maximum_importance'];
        }

        $suggestion['singular_they'] = $organizationGuidelines->singular_they ? 'all_pronouns' : 'he_or_she';
        if ($organizationGuidelines->singular_they_force) {
            $force['singular_they'] = $suggestion['singular_they'];
        }

        $suggestion['show_inspiration_alternatives'] = $organizationGuidelines->show_inspiration_alternatives;
        if ($organizationGuidelines->show_inspiration_alternatives_force) {
            $force['show_inspiration_alternatives'] = $suggestion['show_inspiration_alternatives'];
        }

        $suggestion['gendered_roles_format'] = $organizationGuidelines->gendered_roles_format;
        if ($organizationGuidelines->gendered_roles_format_force) {
            $force['gendered_roles_format'] = $suggestion['gendered_roles_format'];
        }

        $suggestion['german_gender_ending'] = $organizationGuidelines->german_gender_ending;
        if ($organizationGuidelines->german_gender_ending_force) {
            $force['german_gender_ending'] = $suggestion['german_gender_ending'];
        }

        foreach (OrganizationGuidelines::DISABLED_CATEGORIES as $category) {
            if (in_array($category, $organizationGuidelines->disabled_categories)) {
                $suggestion['disabled_categories'][] = $category;
                if (in_array($category, $organizationGuidelines->disabled_categories_force)) {
                    $force['disabled_categories'][] = $category;
                }
            }
        }

        foreach ($organizationGuidelines->preferred_variants as $variant) {
            $lang = substr($variant, 0, 2);
            $suggestion['preferred_variants'][] = $variant;
            if (in_array($lang, $organizationGuidelines->preferred_variants_force)) {
                $force['preferred_variants'][] = $variant;
            }
        }

        $data = [
            'organization' => $team->id,
            'name' => $team->name,
            'plan' => $plan,
            'store_context' => $team->store_context,
            'users' => $users,
            'forced' => $force,
            'suggestion' => $suggestion,
            'false_positives' => $falsePositives,
            'term_replacements' => $termReplacements,
        ];

        $endpoint = config('app.organization_guidelines_endpoint');

        if (empty($endpoint['user'])) {
            $response = Http::post($endpoint['url'], $data);
        } else {
            $response = Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->post($endpoint['url'], $data);
        }

        if (config('app.debug') && $response->failed()) {
            dd($response->body(), $data);
        }
    }
}
