<?php

namespace App\Http\Livewire;

class UserAnalytics extends OrganizationAnalytics
{
    public function mount($user)
    {
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.user-analytics');
    }

    public function chartData()
    {
        //$postHogId = $this->user->posthogId();
        $postHogId = 'DEV_APP_ID';
        if ($postHogId !== 'DEV_APP_ID') {
            $postHogId = \App\Providers\AppServiceProvider::POSTHOG_ID_PREFIX . $postHogId;
        }

        $properties = [
            'type' => 'AND',
            'values' => [
                [
                    'key' => 'request__id',
                    'value' => $postHogId,
                    'operator' => 'exact',
                    'type' => 'event',
                ]
            ]
        ];

        return $this->fetchData($properties);
    }
}
