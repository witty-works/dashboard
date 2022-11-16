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
        return view('analytics');
    }

    public function organization(Request $request)
    {
        return view('analytics');
    }

    public function userApi(Request $request)
    {
        if (empty($request->user())) {
            abort(403);
        }


        $postHogId = config('posthog.dashboard_user_id_override');
        if (empty($postHogId)) {
            $postHogId = $request->user()->posthogId();
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

        return $this->fetchJson($request, $properties);
    }

    public function organizationApi(Request $request)
    {
        if (empty($request->user()) || empty($request->user()->currentTeam)) {
            abort(403);
        }

        $postHogId = config('posthog.dashboard_team_id_override');
        if (empty($postHogId)) {
            $postHogId = $request->user()->currentTeam->posthogId();
        }

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

        return $this->fetchJson($request, $properties);
    }

    protected function fetchData($filter)
    {
        $key = 'posthog:' . md5($this->url) . ':' . md5(serialize($filter));
        if ($this->refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, config('posthog.insights_cache_time'), function () use ($filter) {
            $response = Http::withToken($this->personalApiKey)
                ->post($this->url, $filter);

            return $response->collect()->all();
        });
    }

    protected function fetchEventData($events, $properties, $interval, $math = 'total')
    {
        $filter = [
            'events' => [
                [
                    'properties' => $properties,
                    'math' => $math,
                ]
            ],
            'filter_test_accounts' => false,
            'date_from' => $interval,
        ];

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = $this->fetchData($filter);
            if (isset($response['result'][0])) {
                $data['days'] = $response['result'][0]['days'];
                $data['events'][$event] = array_combine($response['result'][0]['days'], $response['result'][0]['data']);
            }
            $data['last_refresh'][$event] = $response['last_refresh'] ?? null;
        }

        return $data;
    }

    protected function fetchBreakdown($events, $properties, $breakdown, $interval, $math = 'total')
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
            'date_from' => $interval,
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
                $data['last_refresh'][$event] = $response['last_refresh'] ?? null;
            }
        }

        return $data;
    }

    protected function fetchJson(Request $request, $properties)
    {
        $interval = $this->fetchInterval($request);
        $chart = $request->get('chart');
        switch ($chart) {
            case 'dau':
                $events = ['check', 'popover_open', 'alternative', 'ignore'];
                $data = $this->fetchEventData($events, $properties, $interval, 'dau');
                break;
            case 'total':
                $events = ['check', 'popover_open', 'alternative', 'ignore'];
                $data = $this->fetchEventData($events, $properties, $interval);
                break;
            case 'topSubcategories':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data__subcategory', $interval);
                break;
            case 'topWords':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data_text', $interval);
                break;
            default:
                return response()->json(['error' => 400, 'message' => "Unsupported chart type '$chart'"], 400);
                break;
        }

        return response()->json($data);
    }

    protected function fetchInterval(Request $request)
    {
        $maxDays = 30;
        $interval = $request->get('interval', $maxDays);
        $interval = min($maxDays, $interval);

        return "-{$interval}d";
    }
}
