<?php

namespace App\Listeners;

use App\Models\LanguageGuidelines;
use App\Models\Team;
use Laravel\Jetstream\Events\TeamDeleted;
use App\Models\GuidelinesInterface;

class UpdateOrganizationGuidelines extends AbstractUpdateGuidelines
{
    public function handle($event)
    {
        $team = $event->team;

        if ($event instanceof TeamDeleted) {
            $url = '/organization/rules?' . http_build_query(['organization_id' => $team->posthogId()]);
            $this->deleteRules($url);
        } else {
            $url = '/organization/rules';
            $data = $this->getData($team);

            $this->updateRules($url, $data);

            return $data;
        }
    }

    public function getData(Team $team)
    {
        $termReplacements = $this->getTermReplacements(
            $team->termReplacements,
            $team->subscribed(),
            $team->getTermReplacementsCount()
        );

        $falsePositives = $this->getFalsePositives(
            $team->falsePositives,
            $team->subscribed(),
            $team->getFalsePositivesCount()
        );

        $domains = $this->getDomains($team->domains, $team->getDomainListType());

        $plan = $team->planId();

        $guidelines = LanguageGuidelines::firstOrNew(['team_id' => $team->id]);
        $config = self::getConfig($guidelines);
        foreach (GuidelinesInterface::DISABLED_CATEGORIES as $category) {
            $config[$category] = [
                'value' => !in_array($category, $guidelines->disabled_categories),
                'status' => null === $guidelines->disabled_categories_force
                    || in_array($category, $guidelines->disabled_categories_force)
                    ? 'force' : 'suggestion',
            ];
        }

        $data = [
            'id' => $team->posthogId(),
            'name' => $team->name,
            'plan' => $plan,
            'false_positives' => $falsePositives,
            'term_replacements' => $termReplacements,
            'domains' => $domains,
            'config' => $config,
            'store_context' => [
                'value' => $team->subscribed() ? (bool) $team->store_context : true,
                'status' => 'force',
            ]
        ];

        $data['config_hash'] = md5(serialize($data));
        $team->config_hash = $data['config_hash'];
        $team->saveQuietly();

        return $data;
    }
}
