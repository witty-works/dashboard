<?php

namespace App\Http\Controllers;

use App\Helpers\Categories;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use App\Helpers\Posthog;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected $url;
    protected $refresh;

    public function __construct(Request $request)
    {
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

        $properties = PostHog::getUserFilter($request->user());

        return $this->fetchJson($request, $properties);
    }

    public function organizationApi(Request $request)
    {
        $user = $request->user();
        $this->teamAnalyticsAllowed($user);

        $properties = PostHog::getOrganizationFilter($user->currentTeam);

        return $this->fetchJson($request, $properties, $user->currentTeam);
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

            $response = PostHog::fetchData($filter, $this->url);
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

            $response = PostHog::fetchData($filter, $this->url);
            if (isset($response['result'])) {
                foreach ($response['result'] as $value) {
                    $data['events'][$event][$value['breakdown_value']] = $value['aggregated_value'];
                }
                $data['last_refresh'] = $response['last_refresh'];
            }
        }

        return $data;
    }

    protected function numWeeks(DateTime $firstDate, DateTime $secondDate)
    {
        $differenceInDays = $firstDate->diff($secondDate)->days;
        $differenceInWeeks = $differenceInDays / 7;

        return floor($differenceInWeeks);
    }

    protected function fetchJson(Request $request, $properties, $model = null)
    {
        $from = $this->fetchFrom($request);
        $fromPosthog = "-{$from}d";
        $interval = $this->fetchInterval($request);
        $chart = $request->get('chart');

        $filterField = 'response__data__category';
        $filters = $this->fetchCategoryFilters($request);
        if (empty($filters)) {
            $filters = $this->fetchSubcategoryFilters($request);
            $filterField = 'response__data__subcategory';
        }

        if (!empty($filters)) {
            $filterType = $this->fetchFilterType($request);

            foreach ($filters as $filter) {
                $values[] = [
                    'key' => $filterField,
                    'value' => $filter,
                    'operator' => $filterType,
                    'type' => 'event',
                ];
            }

            if ($filterType === 'is_not' || count($values) === 1) {
                $properties['values'] = array_merge($properties['values'], $values);
            } else {
                $properties['values'] = [
                    'type' => 'AND',
                    'values' => [
                        [
                            'type' => 'AND',
                            'values' => $properties['values'],
                        ],
                        [
                            'type' => 'OR',
                            'values' => $values,
                        ]
                    ],
                ];
            }
        }

        switch ($chart) {
            case 'dau':
                $events = ['popover_open', 'alternative', 'ignore', 'learning_bites'];
                $data = $this->fetchEventData($events, $properties, $interval, $fromPosthog, 'dau');

                if ($model instanceof Team) {
                    $fromUserCount = $from + 10;
                    $userCount = Kpi::where('team_id', $model->id)
                        ->where('kpi', Kpi::TEAM_COUNT)
                        ->whereRaw("date > DATE_SUB(NOW(), INTERVAL {$fromUserCount} DAY)")
                        ->select('date', 'value')
                        ->pluck('value', 'date')
                        ->toArray();

                    foreach ($data['events']['popover_open'] as $day => $value) {
                        $value = (int) $value;
                        // the check here is to handle the case when a team adds and removes users over the course of the week
                        if (isset($userCount[$day]) && $userCount[$day] > $value) {
                            $value = $userCount[$day];
                        }
                        // handle missing data in the KPI table, this should eventually never happen
                        if ($value === 0) {
                            $value = $model->getTotalUserCount();
                        }
                        $data['events']['user_count'][$day] = $value;
                    }
                }

                break;
            case 'total':
                $events = ['check', 'popover_open', 'alternative', 'ignore', 'learning_bites'];
                $data = $this->fetchEventData($events, $properties, $interval, $fromPosthog);

                $writingStreak = 0;
                $writingStreakComplete = true;
                foreach ($data['events']['check'] as $day => $value) {
                    // skip everything that isn't start of the week
                    // @TODO honor the users start of the week
                    if ($interval === 'day' && (int)date('w', strtotime($day)) !== 0) {
                        continue;
                    }

                    if ((int)$value === 0) {
                        $writingStreak = 0;
                        $writingStreakComplete = false;
                    }
                    $writingStreak += ($value ? 1 : 0);
                }

                if ($writingStreakComplete) {
                    $columnName = $model instanceof Team ? 'team_id' : 'user_id';
                    $query = Kpi::where($columnName, $model->id)
                        ->where('kpi', Kpi::WRITING_STREAK)
                        ->groupBy('year_date', 'week_date')
                        ->having(DB::raw('SUM(value)'), '=', 0)
                        ->orderBy('year_date', 'DESC')
                        ->orderBy('week_date', 'DESC')
                        ->limit(1)
                        ->select(DB::raw('YEAR(date) AS year_date'), DB::raw('WEEK(date) AS week_date'));

                    // most recent week before the current writing streak started
                    $writingStreakEnd = $query
                        ->first();

                    // user/team has a writing streak since the beginning, ie. they never had a week without a writing streak
                    if (empty($writingStreakEnd)) {
                        $query = $model instanceof Team ? Team::query() : User::query();
                        $query->where('id', $model->id)
                            ->select(DB::raw('YEAR(created_at) AS year_date'), DB::raw('WEEK(created_at) AS week_date'));

                        $writingStreakEnd = $query
                            ->first();
                    }

                    $writingStreakEnd = $writingStreakEnd->toArray();
                    $writingStreakStart = new DateTime();
                    $writingStreakStart->setISODate($writingStreakEnd['year_date'], $writingStreakEnd['week_date']);
                    $writingStreakStart->modify('+7 day');

                    // writing streaks can only have started in December
                    $earliestWritingStreakStart = new DateTime('2022-12-01');

                    if (empty($writingStreakStart) || $writingStreakStart < $earliestWritingStreakStart) {
                        $writingStreakStart = $earliestWritingStreakStart;
                    }

                    $writingStreak = max(
                        $writingStreak,
                        $this->numWeeks($writingStreakStart, new DateTime())
                    );
                }

                unset($data['events']['check']);
                $data['writing_streak'] = $writingStreak;
                break;
            case 'topSubcategories':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data__subcategory', $interval, $fromPosthog);

                $locale = session('locale', 'en');
                foreach ($events as $event) {
                    if (!empty($data['events'][$event])) {
                        $subcategories = [];
                        foreach ($data['events'][$event] as $subcategory => $count) {
                            if (empty(Categories::CATEGORIES[$subcategory])) {
                                continue;
                            }

                            $category = Categories::CATEGORIES[$subcategory];
                            $subcategories[$category['name'][$locale]] = $count;

                            $data['subcategories'][$event][$subcategory] = $category;
                        }
                        $data['events'][$event] = $subcategories;
                    }
                }
                break;
            case 'topWords':
                $events = ['popover_open', 'alternative', 'ignore'];
                $data = $this->fetchBreakdown($events, $properties, 'response__data_text', $interval, $fromPosthog);
                break;
            default:
                return response()->json(['error' => 400, 'message' => "Unsupported chart type '$chart'"], 400);
                break;
        }

        return response()->json($data);
    }

    protected function fetchFilterType(Request $request)
    {
        return $request->get('filter_type', 'exact');
    }

    protected function fetchSubcategoryFilters(Request $request)
    {
        return $request->get('subcategory_filters', []);
    }

    protected function fetchCategoryFilters(Request $request)
    {
        return $request->get('category_filters', []);
    }

    protected function fetchOrthography(Request $request)
    {
        return $request->get('orthography', true);
    }

    protected function fetchInterval(Request $request)
    {
        return $request->get('interval', 'day');
    }

    protected function fetchFrom(Request $request)
    {
        return max(min((int)$request->get('from', '30'), 30), 1);
    }
}
