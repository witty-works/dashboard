<?php

namespace App\Jobs;

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Models\GuidelinesInterface;
use App\Models\LanguageGuidelines;
use App\Models\Team;
use InvalidArgumentException;

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
            throw new InvalidArgumentException("Team id '{$this->id} does not exist.");
        }

        $url = '/organization/configs';
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

        $guidelines = LanguageGuidelines::getLanguageGuidelines(['team_id' => $team->id]);
        $config = self::getConfig($guidelines, !$team->subscribed());

        $config['categories'] = [];
        foreach (GuidelinesInterface::DISABLED_CATEGORIES as $category) {
            $config['categories'][$category] = [
                'value' => !in_array($category, $guidelines->disabled_categories),
                'status' => (null === $guidelines->disabled_categories_force
                    || in_array($category, $guidelines->disabled_categories_force)
                    || !$team->subscribed()
                ) ? 'force' : 'suggestion',
            ];
        }

        foreach ($guidelines->diversityDimensionDrivers as $ddd => $config) {
            $category = $config['category'] ?? null;
            $config['categories'][$ddd] = [
                'value' => !in_array($ddd, $guidelines->disabled_categories),
                'status' => (null === $guidelines->disabled_categories_force
                    || in_array($category, $guidelines->disabled_categories_force)
                    || !$team->subscribed()
                ) ? 'force' : 'suggestion',
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
