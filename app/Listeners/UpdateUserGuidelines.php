<?php

namespace App\Listeners;

use App\Models\LanguageGuidelines;
use App\Models\User;
use App\Events\UserDeleted;

class UpdateUserGuidelines extends AbstractUpdateGuidelines
{
    public function handle($event)
    {
        $user = $event->user;

        if ($event instanceof UserDeleted) {
            $url = '/user/rules?' . http_build_query(['email' => $user->email]);
            $this->deleteRules($url);
        } else {
            $url = '/user/rules';
            $data = $this->getData($user);

            $this->updateRules($url, $data);

            return $data;
        }
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
        ];

        $data['config_hash'] = md5(serialize($data));
        $user->config_hash = $data['config_hash'];
        $user->saveQuietly();

        return $data;
    }
}
