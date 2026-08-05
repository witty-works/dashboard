<?php

namespace App\Jobs;

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
        $termReplacements = $this->getTermReplacements($team->termReplacements);

        $falsePositives = $this->getFalsePositives($team->falsePositives);

        $domains = $this->getDomains($team->domains, $team->getDomainListType());

        $guidelines = LanguageGuidelines::getLanguageGuidelines($team);
        $config = self::getConfig($guidelines, false);

        $config['categories']['orthography'] = [
            'value' => !in_array('orthography', $guidelines->disabled_categories),
            'status' => 'force',
        ];
        foreach ($guidelines->getDiversityDimensionDrivers(null, true) as $ddd => $dddConfig) {
            $config['categories'][$ddd] = [
                'value' => !in_array($ddd, $guidelines->disabled_categories),
                'status' => 'force',
            ];
        }
        $config['force_categories'] = $guidelines->disabled_categories_force;

        $config['store_context'] = [
            'value' => (bool) $team->store_context,
            'status' => 'force',
        ];

        // An installation whose backend has no LLM support reports every team as
        // opted out, whatever the team's own setting says.
        $config['llm_alternatives'] = [
            'value' => config('app.llm_enabled') && (bool) $team->llm_alternatives,
            'status' => 'force',
        ];

        $data = [
            'id' => $team->posthogId(),
            'name' => $team->name,
            'false_positives' => $falsePositives,
            'term_replacements' => $termReplacements,
            'domains' => $domains,
            'config' => $config,
        ];

        $data['config_hash'] = md5(serialize($data));
        $team->config_hash = $data['config_hash'];
        $team->saveQuietly();

        return $data;
    }
}
