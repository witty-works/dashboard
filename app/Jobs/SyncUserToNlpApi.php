<?php

namespace App\Jobs;

use App\Models\LanguageGuidelines;
use App\Models\User;
use InvalidArgumentException;

class SyncUserToNlpApi extends AbstractSyncToNlpApi
{
    protected $id;

    public function __construct(User $user, $queue = 'medium')
    {
        $this->id = $user->id;
        $this->onQueue($queue);
    }

    public function handle()
    {
        $user = User::find($this->id);
        if (!$user instanceof User) {
            throw new InvalidArgumentException("User id '{$this->id} does not exist.");
        }

        $url = '/user/configs';
        $data = $this->getData($user);

        return $this->updateRules($url, $data);
    }

    public function getData(User $user)
    {
        $termReplacements = $this->getTermReplacements($user->termReplacements);

        $falsePositives = $this->getFalsePositives($user->falsePositives);

        $domains = $this->getDomains($user->domains, 'deny');

        $guidelines = LanguageGuidelines::getLanguageGuidelines($user);
        $config = self::getConfig($guidelines, true);

        $config['categories'] = [];
        foreach ($guidelines->getDiversityDimensionDrivers(null, true) as $ddd => $dddConfig) {
            $config['categories'][$ddd] = [
                'value' => !in_array($ddd, $guidelines->disabled_categories),
                'status' => 'force',
            ];
        }

        $data = [
            'id' => $user->posthogId(),
            'email' => $user->email,
            'name' => $user->name,
            'organization_id' => $user->posthogTeamId(),
            'false_positives' => $falsePositives,
            'term_replacements' => $termReplacements,
            'domains' => $domains,
            'config' => $config,
            'notifications' => $user->getNotificationCount(),
            'has_consented_to_mailing' => (bool) $user->has_consented_to_mailing,
            'team_analytics' => $user->team_analytics !== false,
        ];

        $data['config_hash'] = md5(serialize($data));
        $user->config_hash = $data['config_hash'];
        $user->saveQuietly();

        return $data;
    }
}
