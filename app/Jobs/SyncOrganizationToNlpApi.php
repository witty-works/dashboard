<?php

namespace App\Jobs;

use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use App\Models\Team;

class SyncOrganizationToNlpApi extends AbstractSyncToNlpApi
{
    protected $id;

    public function __construct(Team $team, $queue = 'medium')
    {
        $this->id = $team->id;
        $this->onQueue($queue);
    }

    public function handle()
    {
        $team = Team::find($this->id);
        if (!$team instanceof Team) {
            return -1;
        }

        $url = '/organization/rules';
        $data = $this->getData($team);

        return $this->updateRules($url, $data);
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
