<?php

namespace App\Http\Controllers;

use App\Helpers\CategoryDataHelper;
use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use App\Helpers\PosthogHelper;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AnalyticsController extends Controller
{
    protected $refresh;
    protected $categories;
    protected $subcategories;

    public function __construct(Request $request)
    {
        $this->refresh = $request->get('refresh', false) === 'true';
        $this->categories = CategoryDataHelper::loadTableData('categories');
        $this->subcategories = CategoryDataHelper::loadTableData('diversity_dimension_drivers', true);
    }

    protected function getDefaultEvent()
    {
        if (app('impersonate')->isImpersonating()) {
            return 'check_highlights';
        }

        return env('CHECK_HIGHLIGHTS_ENABLED', false) ? 'check_highlights' : 'popover_open';
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
            'default_event' => $this->getDefaultEvent(),
            'is_premium_user' => $user->isPremium(),
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
            'default_event' => $this->getDefaultEvent(),
            'is_premium_user' => $user->currentTeam->isPremium(),
        ]);
    }

    public function userApi(Request $request)
    {
        $user = $request->user();
        if (empty($user)) {
            abort(403);
        }

        $properties = PosthogHelper::getUserFilter($user);

        try {
            return $this->buildJson($request, $properties, $user);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Analytics data temporarily unavailable'], 503);
        }
    }

    public function organizationApi(Request $request)
    {
        $user = $request->user();
        $this->teamAnalyticsAllowed($user);

        $properties = PosthogHelper::getOrganizationFilter($user->currentTeam);

        try {
            return $this->buildJson($request, $properties, $user->currentTeam);
        } catch (RuntimeException $e) {
            return response()->json(['message' => 'Analytics data temporarily unavailable'], 503);
        }
    }

    protected function buildFilter($properties, $interval, $from, $to, $math)
    {
        $properties[] = [
            'key' => '$host',
            'value' => [
                "loop.cloud.microsoft"
            ],
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
            'date_to' => $to,
        ];

        return $filter;
    }

    protected function buildEventData($events, $properties, $interval, $from, $to, $math = 'total')
    {
        $filter = $this->buildFilter($properties, $interval, $from, $to, $math);

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

    protected function buildBreakdown($events, $properties, $breakdown, $interval, $from, $to, $display = null)
    {
        // filter out orthography
        $properties[] = [
            'key' => 'response__data__category',
            'value' => 'orthography',
            'operator' => 'is_not',
            'type' => 'event',
        ];

        $filter = $this->buildFilter($properties, $interval, $from, $to, 'total');

        $filter['breakdown'] = $breakdown;
        $filter['breakdown_type'] = 'hogql';

        $data = [];

        if ($display) {
            $filter['display'] = 'ActionsBarValue';
            $callback = function ($value) {
                return $value['aggregated_value'];
            };
        } else {
            $callback = function ($value) {
                return array_combine($value['days'], $value['data']);
            };
        }

        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = PosthogHelper::fetchData($filter, $this->refresh);
            if (isset($response['result'])) {
                foreach ($response['result'] as $value) {
                    if (!empty($value['breakdown_value']) && strpos($value['breakdown_value'], '$$_posthog_') === false) {
                        $data['events'][$event][$value['breakdown_value']] = $callback($value);
                    }
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

    protected function filterEvents($events, $isPremium)
    {
        if (!is_array($events)) {
            $events = [];
        } else {
            if (!$isPremium) {
                $key = array_search($this->getDefaultEvent(), $events);
                if ($key) {
                    unset($events[$key]);
                }
            }
            if (count($events) > 1) {
                $events = [$events[0]];
            }
        }
        if (empty($events)) {
            $events = $isPremium ? [$this->getDefaultEvent()] : ['popover_open'];
        }

        return $events;
    }

    protected function buildJson(Request $request, $properties, $model)
    {
        $rules = [
            'chart' => 'required|in:dau,total,topSubcategories,topWords',
            'interval' => 'in:day,week,month',
            'from' => 'required',
            'to' => 'nullable',
            'lang' => 'nullable|in:en,de,fr',
            'events' => 'nullable|array|in:check_highlights,popover_open,alternative,ignore,learning_bites',
            'categories' => 'nullable|array|in:' . implode(',', $this->categories->keys()->toArray()),
            'inclusive' => 'nullable|in:inclusive,non_inclusive,both',
        ];

        $validated = $request->validate($rules);

        $chart = $validated['chart'];
        $interval = $validated['interval'] ?? 'day';
        $from = $validated['from'] ?? '30d';
        $to = $validated['to'] ?? '0d';
        $lang = $validated['lang'] ?? null;
        $events = $validated['events'] ?? null;
        $categories = $validated['categories'] ?? [];
        $inclusive = $validated['inclusive'] ?? 'non_inclusive';

        // @TODO make it possible to choose if to group or not
        $group_subcategories = true;

        // BC code
        if (is_numeric($from)) {
            $from = "{$from}d";
        }

        $from = "-{$from}";
        $to = "-{$to}";

        if (!empty($lang)) {
            $properties[] = [
                'key' => 'response__data__language',
                'value' => $lang,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        if (!empty($categories) || $inclusive !== 'both') {
            if (empty($categories)) {
                foreach ($this->categories as $category => $categoryData) {
                    $categories[] = $category;
                }
            }

            $subcategoriesToRemove = [];
            foreach ($this->subcategories as $subcategory => $subcategoryData) {
                if ($subcategory === 'corporate_rules') {
                    if ($inclusive === 'inclusive') {
                        $subcategoriesToRemove[] = $subcategory;
                    }
                    continue;
                }

                if (!in_array($subcategoryData['category'], $categories)) {
                    $subcategoriesToRemove[] = $subcategory;
                } else {
                    if ($subcategoryData['proficiency_level'] === 'inclusive') {
                        if ($inclusive === 'non_inclusive') {
                            $subcategoriesToRemove[] = $subcategory;
                        }
                    } elseif ($inclusive === 'inclusive') {
                        $subcategoriesToRemove[] = $subcategory;
                    }
                }
            }

            $subcategories = $this->subcategories->keys()->toArray();
            $subcategories = array_diff($subcategories, $subcategoriesToRemove);
            $subcategories = array_values($subcategories);

            $properties[] = [
                'key' => 'response__data__subcategory',
                'value' => $subcategories,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        switch ($chart) {
            case 'dau':
                if ($model->isPremium()) {
                    $events = ['popover_open', 'alternative', 'ignore', 'learning_bites'];
                } elseif (empty($events)) {
                    $events = [$this->getDefaultEvent()];
                }

                $key = reset($events);
                $data = $this->buildEventData(
                    $events,
                    $properties,
                    $interval,
                    $from,
                    $to,
                    'dau'
                );

                if ($model instanceof Team && !empty($data['events'][$key])) {
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

                    foreach ($data['events'][$key] as $day => $value) {
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
                if (empty($events)) {
                    $events = ['popover_open', 'alternative', 'ignore', 'learning_bites'];
                    if ($model->isPremium()) {
                        array_unshift($events, $this->getDefaultEvent());
                    }
                }

                array_unshift($events, 'check');

                $data = $this->buildEventData(
                    $events,
                    $properties,
                    $interval,
                    $from,
                    $to,
                );

                $writingStreak = 0;
                $writingStreakComplete = true;
                if (!empty($data['events']['check'])) {
                    foreach ($data['events']['check'] as $day => $value) {
                        // skip everything that isn't start of the week
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
                $events = $this->filterEvents($events, $model->isPremium());

                $data = $this->buildBreakdown(
                    $events,
                    $properties,
                    'properties.response__data__subcategory',
                    $interval,
                    $from,
                    $to,
                );

                foreach ($events as $event) {
                    if (!empty($data['events'][$event])) {
                        foreach ($data['events'][$event] as $subcategory => $counts) {
                            $key = str_replace(['advanced_', '_advanced'], ['', ''], $subcategory);
                            if (empty($this->subcategories[$key])) {
                                continue;
                            }

                            if ($group_subcategories && !empty($data['events'][$event][$key]['counts'])) {
                                foreach ($counts as $day => $count) {
                                    if (empty($data['events'][$event][$key]['counts'][$day])) {
                                        $data['events'][$event][$key]['counts'][$day] = 0;
                                    }
                                    $data['events'][$event][$key]['counts'][$day] += $count;
                                }
                            } else {
                                $category = $this->subcategories[$key];
                                if (empty($category['translation'])) {
                                    $category['translation'] = reset($category['translations']);
                                }

                                if ($key == 'corporate_rules') {
                                    $category['translation']['canonical_url'] = route($model instanceof Team ? 'teams.dictionary' : 'user.dictionary');
                                }

                                $data['events'][$event][($group_subcategories ? $key : $subcategory)] = [
                                    'counts' => $counts,
                                    'name' => $category['translation']['hs_name'],
                                    'url' => $category['translation']['canonical_url'] ?? null,
                                ];
                            }

                            if ($group_subcategories && $key != $subcategory) {
                                unset($data['events'][$event][$subcategory]);
                            }
                        }
                    }
                }
                break;
            case 'topWords':
                $events = $this->filterEvents($events, $model->isPremium());

                $data = $this->buildBreakdown(
                    $events,
                    $properties,
                    'coalesce(properties.response__data__text, properties.response__data_text)',
                    $interval,
                    $from,
                    $to,
                    'ActionsBarValue',
                );
                break;
            default:
                return response()->json(['error' => 400, 'message' => "Unsupported chart type '$chart'"], 400);
                break;
        }

        return response()->json($data);
    }
}
