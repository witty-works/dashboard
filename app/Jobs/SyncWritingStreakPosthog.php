<?php

namespace App\Jobs;

use App\Helpers\PosthogHelper;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\RateLimitedMiddleware\RateLimited;

class SyncWritingStreakPosthog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $model;
    protected $date;

    public function __construct($model, $date)
    {
        $this->id = $model->id;
        $this->model = $model instanceof Team ? 'team' : 'user';
        $this->date = $date;
    }

    public function retryUntil()
    {
        return now()->addHour(5);
    }

    public function middleware()
    {
        $rateLimitedMiddleware = new RateLimited(false);

        $rateLimitedMiddleware
            ->allow(config('posthog.rate.limit'))
            ->everySeconds(config('posthog.rate.interval_seconds'));

        return [$rateLimitedMiddleware];
    }

    public function handle()
    {
        $model = $this->model === 'team'
            ? Team::find($this->id) : User::find($this->id);

        if ($model) {
            Kpi::storeKpi($model, Kpi::WRITING_STREAK, $this->getWritingStreak($model), $this->date);
        }
    }

    protected function getWritingStreak($model)
    {
        $properties = $model instanceof Team
            ? PosthogHelper::getOrganizationFilter($model)
            : PosthogHelper::getUserFilter($model);

        $filter = [
            'events' => [
                [
                    'properties' => $properties,
                    'math' => 'total',
                    'id' => 'check',
                ]
            ],
            'filter_test_accounts' => false,
            'interval' => 'day',
            'date_from' => $this->date,
            'date_to' => $this->date,
        ];

        $response = PosthogHelper::fetchData($filter);
        foreach ($response['result'][0]['data'] as $data) {
            if ($data) {
                return 1;
            }
        }

        return 0;
    }
}
