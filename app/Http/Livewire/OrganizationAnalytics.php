<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class OrganizationAnalytics extends Component
{
    public const CHART_TYPES = [
        '' => '',
        'dau' => 'chart.dau',
        'total' => 'chart.total',
        'topSubcategories' => 'chart.topSubcategories',
        'topWords' => 'chart.topWords',
    ];

    protected $personalApiKey;
    protected $projectId;
    protected $cacheTimeInSeconds = 3600;

    public $refresh = false;
    public $chart;
    public $chartData;

    public function __construct()
    {
        $this->personalApiKey = config('posthog.personal_api_key');
        $this->projectId = config('posthog.project_id');
    }

    public function mount($team)
    {
        $this->team = $team;
    }

    public function render()
    {
        return view('livewire.organization-analytics');
    }

    public function updateChartData()
    {
        dd($this->chart);
        //$postHogId = $this->team->posthogId();
        $postHogId = \App\Providers\AppServiceProvider::POSTHOG_ID_PREFIX . '17';

        $properties = [
            'type' => 'AND',
            'values' => [
                [
                    'key' => 'response__groupId',
                    'value' => $postHogId,
                    'operator' => 'exact',
                    'type' => 'event',
                ]
            ]
        ];

        $this->chartData = $this->fetchData($properties);
    }

    protected function fetchData($properties)
    {
        switch ($this->chart) {
            case 'dau':
                $events = ['check', 'popover_open', 'alternative', 'ignore'];
                $data = $this->fetchEventData($events, $properties, 'dau');
                break;
            case 'total':
                $events = ['check', 'popover_open', 'alternative', 'ignore'];
                $data = $this->fetchEventData($events, $properties);
                break;
            case 'topSubcategories':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data__subcategory');
                break;
            case 'topWords':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data_text');
                break;
            default:
                $message = "Unsupported chart type '{$this->chart}'";
                throw ValidationException::withMessages(['chart' => $message]);
        }

        return $data;
    }

    protected function fetchFromPostHog($filter)
    {
        $url = config('posthog.host') . "/api/projects/{$this->projectId}/insights/trend";
        $cacheKey = 'posthog:' . md5($url) . ':' . md5(serialize($filter));
        if ($this->refresh) {
            Cache::forget($cacheKey);
            $this->refresh = false;
        }

        return Cache::remember($cacheKey, $this->cacheTimeInSeconds, function () use ($filter, $url) {
            $response = Http::withToken($this->personalApiKey)
                ->post($url, $filter);


            return $response->collect()->all();
        });
    }

    protected function fetchEventData($events, $properties, $math = 'total')
    {
        $filter = [
            'events' => [
                [
                    'properties' => $properties,
                    'math' => $math,
                ]
            ],
            'filter_test_accounts' => false,
            'date_from' => '-30d',
        ];

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = $this->fetchFromPostHog($filter);
            if (isset($response['result'][0])) {
                $data['days'] = $response['result'][0]['days'];
                $data['events'][$event] = array_combine($response['result'][0]['days'], $response['result'][0]['data']);
            }
            $data['last_refresh'][$event] = $response['last_refresh'];
        }

        return $data;
    }

    protected function fetchBreakdown($events, $properties, $breakdown, $math = 'total')
    {
        $properties['values'][] = [
            'key' => 'request__data_category',
            'value' => 'orthography',
            'operator' => 'is_not',
            'type' => 'event',
        ];

        $filter = [
            'events' => [
                [
                    'properties' => $properties,
                    'math' => $math,
                ]
            ],
            'filter_test_accounts' => false,
            'date_from' => '-30d',
            'display' => 'ActionsBarValue',
            'breakdown' => $breakdown,
        ];

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = $this->fetchFromPostHog($filter);
            if (isset($response['result'])) {
                foreach ($response['result'] as $value) {
                    $data['events'][$event][$value['breakdown_value']] = $value['aggregated_value'];
                }
                $data['last_refresh'][$event] = $response['last_refresh'];
            }
        }

        return $data;
    }
}
