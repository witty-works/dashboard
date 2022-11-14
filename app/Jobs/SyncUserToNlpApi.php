<?php

namespace App\Jobs;

use App\Models\LanguageGuidelines;
use App\Models\User;

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
            return -1;
        }

        $url = '/user/rules';
        $data = $this->getData($user);

        return $this->updateRules($url, $data);
    }

    public function getData(User $user)
    {
        $termReplacements = $this->getTermReplacements(
            $user->termReplacements,
            $user->subscribed(),
            $user->getTermReplacementsCount()
        );

        $falsePositives = $this->getFalsePositives(
            $user->falsePositives,
            $user->subscribed(),
            $user->getFalsePositivesCount()
        );

        $domains = $this->getDomains($user->domains, 'deny');

        $config = self::getConfig(LanguageGuidelines::firstOrNew(['user_id' => $user->id]));

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
            'team_analytics' => $user->subscribed() ? $user->team_analytics !== false : true,
        ];

        $data['config_hash'] = md5(serialize($data));
        $user->config_hash = $data['config_hash'];
        $user->saveQuietly();

        return $data;
    }
}
