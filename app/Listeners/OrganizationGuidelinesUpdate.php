<?php

namespace App\Listeners;

use App\Events\OrganizationGuidelinesUpdated;
use App\Models\OrganizationGuidelines;
use Illuminate\Support\Facades\Http;

class OrganizationGuidelinesUpdate
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(OrganizationGuidelinesUpdated $event)
    {
        $organizationGuidelines = $event->team->organizationGuidelines;

        $force = [
            'store_context' => (bool) $organizationGuidelines->store_context,
        ];
        $suggestion = [];

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
            'organization' => $event->team->id,
            'users' => $event->team->allUsers()->pluck('email')->toArray(),
            'forced' => $force,
            'suggestion' => $suggestion,
            'false_positive' => $event->team->falsePositives()->pluck('false_positive')->toArray(),
        ];

        $endpoint = config('app.organization_guidelines_endpoint');

        if (empty($endpoint['user'])) {
            Http::post($endpoint['url'], $data);
        } else {
            Http::withBasicAuth($endpoint['user'], $endpoint['password'])
                ->post($endpoint['url'], $data);
        }
    }
}
