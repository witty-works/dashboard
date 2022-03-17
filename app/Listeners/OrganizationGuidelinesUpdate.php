<?php

namespace App\Listeners;

use App\Events\OrganizationGuidelinesUpdated;
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
        $organizationGuidelines = [
            'store_context' => (bool) $organizationGuidelines->store_context,
            'preferred_variants' => json_decode($organizationGuidelines->preferred_variants),
            'german_gender_ending' => $organizationGuidelines->german_gender_ending,
            'disabled_categories' => json_decode($organizationGuidelines->disabled_categories),
            'gendered_roles_format' => $organizationGuidelines->gendered_roles_format,
            'singular_they' => $organizationGuidelines->singular_they ? 'all_pronouns' : 'he_or_she',
            'maximum_importance' => $organizationGuidelines->expert_mode ? 3 : 2,
        ];

        $data = [
            'organization' => $event->team->id,
            'users' => $event->team->allUsers()->pluck('email')->toArray(),
            'forced' => $organizationGuidelines,
            'suggestion' => [],
            'false_positive' => $event->team->falsePositives()->pluck('false_positive')->toArray(),
        ];

        Http::post(config('app.organization_guidelines_endpoint'), $data);
    }
}
