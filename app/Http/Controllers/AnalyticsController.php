<?php

namespace App\Http\Controllers;

use App\Console\Commands\SyncToHubspotCategoriesCommand;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use App\Helpers\PosthogHelper;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected $refresh;
    protected $categories;
    protected $subcategories;

    public function __construct(Request $request)
    {
        $this->refresh = $request->get('refresh', false);
        $this->categories = SyncToHubspotCategoriesCommand::loadTableData('categories');
        $this->subcategories = SyncToHubspotCategoriesCommand::loadTableData('diversity_dimension_drivers');
    }

    public function user(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            abort(403);
        }

        return view('analytics', [
            'user' => $user,
            'categories' => $this->categories,
        ]);
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
            'categories' => $this->categories,
        ]);
    }

    public function userApi(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            abort(403);
        }

        $properties = PosthogHelper::getUserFilter($user);

        return $this->buildJson($request, $properties, $user);
    }

    public function organizationApi(Request $request)
    {
        $user = $request->user();
        $this->teamAnalyticsAllowed($user);

        $properties = PosthogHelper::getOrganizationFilter($user->currentTeam);

        return $this->buildJson($request, $properties, $user->currentTeam);
    }

    protected function buildFilter($properties, $filters, $interval, $from, $math)
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

        if (!empty($filters)) {
            $filter['properties'] = [
                'type' => 'AND',
                'values' => [
                    [
                        'type' => 'OR',
                        'values' => $filters
                    ]
                ]
            ];
        }

        return $filter;
    }

    protected function buildEventData($events, $properties, $filters, $interval, $from, $math = 'total')
    {
        $filter = $this->buildFilter($properties, $filters, $interval, $from, $math);

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = PosthogHelper::fetchData($filter, $this->refresh);
            if (!empty($response['result'][0])) {
                $data['events'][$event] = array_combine($response['result'][0]['days'], $response['result'][0]['data']);
            }

            $data['last_refresh'] = $response['last_refresh'];
        }

        return $data;
    }

    protected function buildBreakdown($events, $properties, $filters, $breakdown, $interval, $from, $math = 'total')
    {
        if (empty($filters)) {
            // filter out orthography by default
            $properties[] = [
                'key' => 'response__data__category',
                'value' => 'orthography',
                'operator' => 'is_not',
                'type' => 'event',
            ];
        }

        $filter = $this->buildFilter($properties, $filters, $interval, $from, $math);

        $filter['display'] = 'ActionsBarValue';
        $filter['breakdown'] = $breakdown;

        if (!empty($filters)) {
            $filter['properties'] = [
                'type' => 'AND',
                'values' => [
                    [
                        'type' => 'OR',
                        'values' => $filters
                    ]
                ]
            ];
        }

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = PosthogHelper::fetchData($filter, $this->refresh);
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

    protected function buildJson(Request $request, $properties, $model)
    {
        $rules = [
            'chart' => 'required|in:dau,total,topSubcategories,topWords',
            'interval' => 'in:day,week,month',
            'from' => 'required',
            'lang' => 'nullable|in:en,de',
            'events' => 'nullable|array|in:check,popover_open,alternative,ignore,learning_bites',
            'categories' => 'nullable|array|in:' . implode(',', $this->categories->keys()->toArray()),
            'subcategories' => 'nullable|array|in:' . implode(',', $this->subcategories->keys()->toArray()),
        ];

        $validated = $request->validate($rules);

        $chart = $validated['chart'];
        $interval = $validated['interval'] ?? 'day';
        $from = $validated['from'] ?? '30d';
        $lang = $validated['lang'] ?? null;
        $events = $validated['events'] ?? null;
        $categories = $validated['categories'] ?? [];
        $subcategories = $validated['subcategories'] ?? [];

        // BC code
        if (is_numeric($from)) {
            $from = "{$from}d";
        }

        $from = "-{$from}";

        if (!empty($lang)) {
            $properties[] = [
                'key' => 'response__data__language',
                'value' => $lang,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        $filters = [];
        if (!empty($categories)) {
            $filters[] = [
                'key' => 'response__data__category',
                'value' => $categories,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        if (!empty($subcategories)) {
            $filters[] = [
                'key' => 'response__data__subcategory',
                'value' => $subcategories,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        switch ($chart) {
            case 'dau':
                if (!is_array($events)) {
                    $events = ['popover_open', 'alternative', 'ignore', 'learning_bites'];
                }

                $data = $this->buildEventData($events, $properties, $filters, $interval, $from, 'dau');
                if ($model instanceof Team && !empty($data['events']['popover_open'])) {
                    // handle "-30d" => 30 | "-3w" => 21 | "-5m" => 150
                    switch (substr($from, -1)) {
                        case 'm':
                            $multiplier = -30;
                            break;
                        case 'w':
                            $multiplier = -7;
                            break;
                        case 'd':
                        default:
                            $multiplier = -1;
                            break;
                    }
                    $fromUserCount = ((int) $from * $multiplier) + 10;

                    $userCount = Kpi::where('team_id', $model->id)
                        ->where('kpi', Kpi::TEAM_COUNT)
                        ->whereRaw("date > DATE_SUB(NOW(), INTERVAL {$fromUserCount} DAY)")
                        ->select('date', 'value')
                        ->pluck('value', 'date')
                        ->toArray();

                    foreach ($data['events']['popover_open'] as $day => $value) {
                        $value = (int) $value;
                        // the check here is to handle the case when a team adds
                        // and removes users over the course of the week
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
                if (!is_array($events)) {
                    $events = ['check', 'popover_open', 'alternative', 'ignore', 'learning_bites'];
                } else {
                    array_unshift($events, 'check');
                }

                $data = $this->buildEventData($events, $properties, $filters, $interval, $from);

                $writingStreak = 0;
                $writingStreakComplete = true;
                if (!empty($data['events']['check'])) {
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

                    // user/team has a writing streak since the beginning,
                    // ie. they never had a week without a writing streak
                    if (empty($writingStreakEnd)) {
                        $query = $model instanceof Team ? Team::query() : User::query();
                        $query->where('id', $model->id)
                            ->select(
                                DB::raw('YEAR(created_at) AS year_date'),
                                DB::raw('WEEK(created_at) AS week_date')
                            );

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
                if (!is_array($events)) {
                    $events = ['popover_open', 'alternative', 'ignore'];
                }

                $data = $this->buildBreakdown(
                    $events,
                    $properties,
                    $filters,
                    'response__data__subcategory',
                    $interval,
                    $from
                );

                foreach ($events as $event) {
                    if (!empty($data['events'][$event])) {
                        $subcategories = [];
                        foreach ($data['events'][$event] as $subcategory => $count) {
                            if (empty($this->subcategories[$subcategory]['translation']['hs_name'])) {
                                continue;
                            }

                            $category = $this->subcategories[$subcategory];
                            $subcategories[$category['translation']['hs_name']] = $count;

                            $data['subcategories'][$event][$subcategory] = $category;
                        }
                        $data['events'][$event] = $subcategories;
                    }
                }
                break;
            case 'topWords':
                if (!is_array($events)) {
                    $events = ['popover_open', 'alternative', 'ignore'];
                }

                $data = $this->buildBreakdown($events, $properties, $filters, 'response__data_text', $interval, $from);
                break;
            default:
                return response()->json(['error' => 400, 'message' => "Unsupported chart type '$chart'"], 400);
                break;
        }

        return response()->json($data);
    }
}
