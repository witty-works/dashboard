<?php

namespace App\Http\Controllers;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AnalyticsController extends Controller
{
    protected $personalApiKey;
    protected $url;
    protected $refresh;

    public function __construct(Request $request)
    {
        $this->personalApiKey = config('posthog.personal_api_key');

        $projectId = $request->get('project_id', config('posthog.project_id'));
        $this->url = config('posthog.host') . "/api/projects/$projectId/insights/trend";
        $this->refresh = $request->get('refresh', false);
    }

    public function user(Request $request)
    {
        $postHogId = $request->user()->posthogId();
        $postHogId = $request->get('id', 'DEV_APP_ID');
        if ($postHogId !== 'DEV_APP_ID') {
            $postHogId = AppServiceProvider::POSTHOG_ID_PREFIX . $postHogId;
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

        return $this->fetchAndRender($request, $properties);
    }

    public function organization(Request $request)
    {
        $postHogId = $request->user()->currentTeam->posthogId();
        $postHogId = AppServiceProvider::POSTHOG_ID_PREFIX . $request->get('id', '17');

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

        return $this->fetchAndRender($request, $properties);
    }

    protected function fetchData($filter)
    {
        $key = 'posthog:' . md5($this->url) . ':' . md5(serialize($filter));
        if ($this->refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, 3600, function () use ($filter) {
            $response = Http::withToken($this->personalApiKey)
                ->post($this->url, $filter);


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

            $response = $this->fetchData($filter);
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

            $response = $this->fetchData($filter);
            if (isset($response['result'])) {
                foreach ($response['result'] as $value) {
                    $data['events'][$event][$value['breakdown_value']] = $value['aggregated_value'];
                }
                $data['last_refresh'][$event] = $response['last_refresh'];
            }
        }

        return $data;
    }

    protected function fetchAndRender(Request $request, $properties)
    {
        $events = ['check', 'popover_open', 'alternative', 'ignore'];
        $dataDau = $this->fetchEventData($events, $properties, 'dau');
        $dataTotal = $this->fetchEventData($events, $properties);

        $events = ['popover_open', 'alternative', 'ignore'];
        $topSubcategories = $this->fetchBreakdown($events, $properties, 'response__data__subcategory');

        $topWords = $this->fetchBreakdown($events, $properties, 'response__data_text');

        if ($this->refresh) {
            return redirect()->to($request->fullUrlWithQuery(['refresh' => null]));
        }

        return view('analytics', [
            'dataDau' => $dataDau,
            'dataTotal' => $dataTotal,
            'topSubcategories' => $topSubcategories,
            'topWords' => $topWords,
        ]);
    }
}
