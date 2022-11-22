<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
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
        $user = $request->user();
        if (empty($user)) {
            abort(403);
        }

        return view('analytics', ['user' => $user]);
    }

    protected function teamAnalyticsAllowed(User $user = null)
    {
        if (
            empty($user)
            || empty($user->currentTeam)
            || (!$user->hasTeamPermission($user->currentTeam, 'edit_guidelines')
                && !$user->currentTeam->user_access_to_team_analytics
            )
        ) {
            abort(403);
        }
    }

    public function organization(Request $request)
    {
        $user = $request->user();
        $this->teamAnalyticsAllowed($user);

        return view('analytics', [
            'team' => $user->currentTeam,
            'team_edit' => $user->hasTeamPermission($user->currentTeam, 'edit_guidelines'),
        ]);
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
        $user = $request->user();
        $this->teamAnalyticsAllowed($user);

        $postHogId = config('posthog.dashboard_team_id_override');
        if (empty($postHogId)) {
            $postHogId = $user->currentTeam->posthogId();
        }

        $properties = [
            'type' => 'AND',
            'values' => [
                [
                    'key' => 'response__organizationId',
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

            $data = $response->collect()->all();
            $data['last_refresh'] = Carbon::now();
            return $data;
        });
    }

    protected function fetchEventData($events, $properties, $interval, $from, $math = 'total')
    {
        $filter = [
            'events' => [
                [
                    'properties' => $properties,
                    'math' => $math,
                ]
            ],
            'filter_test_accounts' => false,
            'interval' => $interval,
            'date_from' => $from,
        ];

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = $this->fetchData($filter);
            if (isset($response['result'][0])) {
                $data['events'][$event] = array_combine($response['result'][0]['days'], $response['result'][0]['data']);
            }
            $data['last_refresh'] = $response['last_refresh'];
        }

        return $data;
    }

    protected function fetchBreakdown($events, $properties, $breakdown, $interval, $from, $math = 'total')
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
            'interval' => $interval,
            'date_from' => $from,
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
                $data['last_refresh'] = $response['last_refresh'];
            }
        }

        return $data;
    }

    protected function fetchJson(Request $request, $properties)
    {
        $interval = $this->fetchInterval($request);
        $from = $this->fetchFrom($request);
        $chart = $request->get('chart');
        switch ($chart) {
            case 'dau':
                $events = ['check', 'popover_open', 'alternative', 'ignore', 'learning_bites'];
                $data = $this->fetchEventData($events, $properties, $interval, $from, 'dau');
                break;
            case 'total':
                $events = ['check', 'popover_open', 'alternative', 'ignore', 'learning_bites'];
                $data = $this->fetchEventData($events, $properties, $interval, $from);
                break;
            case 'topSubcategories':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data__subcategory', $interval, $from);
                foreach ($events as $event) {
                    if (!empty($data['events'][$event])) {
                        $subcategories = [];
                        foreach ($data['events'][$event] as $subcategory => $count) {
                            $subcategory = ucwords(str_replace('_', ' ', $subcategory));
                            $subcategories[$subcategory] = $count;
                        }
                        $data['events'][$event] = $subcategories;
                    }
                }
                break;
            case 'topWords':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data_text', $interval, $from);
                break;
            default:
                return response()->json(['error' => 400, 'message' => "Unsupported chart type '$chart'"], 400);
                break;
        }

        return response()->json($data);
    }

    protected function fetchInterval(Request $request)
    {
        return $request->get('interval', 'day');
    }

    protected function fetchFrom(Request $request)
    {
        $maxFrom = 30;
        $from = $request->get('from', $maxFrom);
        $from = min($maxFrom, $from);

        return "-{$from}d";
    }
}
