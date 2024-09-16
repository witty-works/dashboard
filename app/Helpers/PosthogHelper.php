<?php

namespace App\Helpers;

use App\Models\Kpi;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use DateTime;

class PosthogHelper
{
    public const POSTHOG_ORGANIZATION_TYPE = 'organization';
    public const STORE_TEAM_DOMAIN = 'team_store_domain';
    public const STORE_DOMAIN = 'store_domain';
    public const STORE_TEAM_FALSE_POSITIVE = 'team_store_false_positive';
    public const STORE_FALSE_POSITIVE = 'store_false_positive';
    public const STORE_TEAM_TERM_REPLACEMENT = 'team_store_term_replacement';
    public const STORE_TERM_REPLACEMENT = 'store_term_replacement';
    public const STORE_TEAM_LANGUAGE = 'team_store_language';
    public const STORE_LANGUAGE = 'store_language';
    public const ADDED_TEAM_MEMBER = 'added_team_member';
    public const JOINED_TEAM = 'joined_team';

    public static function getInsightsUrl()
    {
        $projectId = config('posthog.project_id');

        return config('posthog.host') . "/api/projects/$projectId/insights/trend";
    }

    public static function getPersonsUrl()
    {
        $projectId = config('posthog.project_id');

        return config('posthog.host') . "/api/projects/$projectId/persons/trends";
    }

    public static function fetchWritingStreak($date, $offset, $organizations = false)
    {
        $url = self::getPersonsUrl();

        $query = [
            'date_from' => $date . 'T00:00:00+00:00',
            'date_to' => $date . 'T23:59:59.999999+00:00',
            'entity_id' => 'check',
            'entity_type' => 'events',
            'entity_math' => $organizations ? 'unique_group' : 'dau',
            'offset' => $offset,
            'interval' => 'day',
            'refresh' => 'true',
        ];

        if ($organizations) {
            $query['events'] = '[{"id": "check", "type": "events", "order": 0, "name": "check", "custom_name": null, "math": "unique_group", "math_property": null, "math_group_type_index": 0, "properties": {"type": "AND", "values": [{"key": "$group_0", "operator": "is_not", "type": "event", "value": ""}]}}]';
        } else {
            $query['events'] = '[{"id": "check", "type": "events", "order": 0, "name": "check", "custom_name": null, "math": "dau", "math_property": null, "math_group_type_index": null, "properties": {}}]';
            $query['properties'] = '{"type": "AND", "values": [{"key": "dashboard_id", "operator": "is_set", "type": "person", "value": "is_set"}]}';
        }

        $response = Http::withToken(config('posthog.personal_api_key'))
            ->get($url, $query);

        if ($response->failed()) {
            throw new RuntimeException("Posthog returned status code:{$response->status()}\n" . serialize($query));
        }

        return $response->collect()->all();
    }

    public static function fetchData($filter, $refresh = false)
    {
        $url = self::getInsightsUrl();

        $key = 'posthog:' . md5($url) . ':' . md5(serialize($filter));
        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, config('posthog.insights_cache_time'), function () use ($url, $filter) {
            if ($filter['events'][0]['id'] === 'check') {
                foreach ($filter['events'][0]['properties'] as $propertyKey => $property) {
                    if ($property['key'] === 'response__data__language') {
                        $filter['events'][0]['properties'][$propertyKey]['key'] = 'response__language';
                        break;
                    }
                }
            }

            $response = Http::withToken(config('posthog.personal_api_key'))
                ->post($url, $filter);

            if ($response->failed()) {
                throw new RuntimeException("Posthog returned status code:{$response->status()}\n" . serialize($filter));
            }

            $data = $response->collect()->all();
            $data['last_refresh'] = Carbon::now();

            return $data;
        });
    }

    public static function getOrganizationFilter(Team $team)
    {
        $postHogId = config('posthog.dashboard_team_id_override');
        if (empty($postHogId)) {
            $postHogId = $team->posthogId();
        }

        return [
            [
                'key' => 'response__organizationId',
                'value' => $postHogId,
                'operator' => 'exact',
                'type' => 'event',
            ]
        ];
    }

    public static function getUserFilter(User $user)
    {
        $postHogId = config('posthog.dashboard_user_id_override');
        if (empty($postHogId)) {
            $postHogId = $user->posthogId();
        }

        return [
            [
                'key' => 'dashboard_id',
                'value' => $postHogId,
                'operator' => 'exact',
                'type' => 'person',
            ]
        ];
    }

    public static function getDefaultEvent()
    {
        if (app('impersonate')->isImpersonating()) {
            return 'check_highlights';
        }

        return env('CHECK_HIGHLIGHTS_ENABLED', false) ? 'check_highlights' : 'popover_open';
    }

    protected static function buildFilter($properties, $filters, $interval, $from, $to, $math)
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
            'date_from' => $from . 'T00:00:00+00:00',
            'date_to' => $to . 'T23:59:59.999999+00:00',
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

    protected static function buildEventData($events, $properties, $filters, $interval, $from, $to, $refresh = false, $math = 'total')
    {
        $filter = self::buildFilter($properties, $filters, $interval, $from, $to, $math);

        $data = [];
        foreach ($events as $event) {
            $filter['events'][0]['id'] = $event;

            $response = PosthogHelper::fetchData($filter, $refresh);
            if (!empty($response['result'][0])) {
                $data['events'][$event] = array_combine($response['result'][0]['days'], $response['result'][0]['data']);
            }

            $data['last_refresh'] = $response['last_refresh'];
        }

        return $data;
    }

    protected static function buildBreakdown($events, $properties, $filters, $breakdown, $interval, $from, $to, $refresh = false, $display = null)
    {
        // filter out orthography
        $properties[] = [
            'key' => 'response__data__category',
            'value' => 'orthography',
            'operator' => 'is_not',
            'type' => 'event',
        ];

        $filter = self::buildFilter($properties, $filters, $interval, $from, $to, 'total');

        $filter['breakdown'] = $breakdown;
        $filter['breakdown_type'] = 'hogql';

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

            $response = PosthogHelper::fetchData($filter, $refresh);
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

    protected static function numWeeks(DateTime $firstDate, DateTime $secondDate)
    {
        $differenceInDays = $firstDate->diff($secondDate)->days;
        $differenceInWeeks = $differenceInDays / 7;

        return floor($differenceInWeeks);
    }

    protected static function filterEvents($events, $isPremium)
    {
        if (!is_array($events)) {
            $events = [];
        } else {
            if (!$isPremium) {
                $key = array_search(self::getDefaultEvent(), $events);
                if ($key) {
                    unset($events[$key]);
                }
            }
            if (count($events) > 1) {
                $events = [$events[0]];
            }
        }
        if (empty($events)) {
            $events = $isPremium ? [self::getDefaultEvent()] : ['popover_open'];
        }

        return $events;
    }

    public static function getData($params, $properties, $model, $refresh, $categories, $subcategories)
    {
        $chart = $params['chart'];
        $interval = $params['interval'] ?? 'day';
        $from = $params['from'] ?? '30d';
        $to = $params['to'] ?? '0d';
        $lang = $params['lang'] ?? null;
        $events = $params['events'] ?? null;
        $categories_filter = $params['categories'] ?? [];
        $subcategories_filter = $params['subcategories'] ?? [];
        // @TODO make it possible to choose if to group or not
        $group_subcategories = $params['group_subcategories'] ?? true;
        $inclusive = $params['inclusive'] ?? 'non_inclusive';

        // BC code
        if (is_numeric($from)) {
            $from = "{$from}d";
        }

        if (preg_match('/^\d+[my]$/', $from)) {
            $from = "-{$from}";
        }
        if (preg_match('/^\d+[my]$/', $from)) {
            $to = "-{$to}";
        }

        if (!empty($lang)) {
            $properties[] = [
                'key' => 'response__data__language',
                'value' => $lang,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        $filters = [];
        if (!empty($categories_filter) && count($categories_filter) != $categories->count()) {
            $filters[] = [
                'key' => 'response__data__category',
                'value' => $categories_filter,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        if ($inclusive !== 'both') {
            $subcategoriesToRemove = [];
            foreach ($subcategories as $subcategory => $subcategoryData) {
                $proficiencyLevel = $subcategoryData['proficiency_level'] ?? 'corporate_rules';
                if ($proficiencyLevel === 'inclusive') {
                    if ($inclusive === 'non_inclusive') {
                        $subcategoriesToRemove[] = $subcategory;
                    }
                } elseif ($inclusive === 'inclusive') {
                    $subcategoriesToRemove[] = $subcategory;
                }
            }

            if (empty($subcategories_filter)) {
                $subcategories_filter = $subcategories->keys()->toArray();
            }

            $subcategories_filter = array_diff($subcategories_filter, $subcategoriesToRemove);
            $subcategories_filter = array_values($subcategories_filter);
        }

        if (!empty($subcategories_filter)) {
            $filters[] = [
                'key' => 'response__data__subcategory',
                'value' => $subcategories_filter,
                'operator' => 'exact',
                'type' => 'event',
            ];
        }

        switch ($chart) {
            case 'dau':
                if ($model->isPremium()) {
                    $events = ['popover_open', 'alternative', 'ignore', 'learning_bites'];
                } elseif (empty($events)) {
                    $events = [self::getDefaultEvent()];
                }

                $key = reset($events);
                $data = self::buildEventData(
                    $events,
                    $properties,
                    $filters,
                    $interval,
                    $from,
                    $to,
                    $refresh,
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
                        array_unshift($events, self::getDefaultEvent());
                    }
                }

                array_unshift($events, 'check');

                $data = self::buildEventData(
                    $events,
                    $properties,
                    $filters,
                    $interval,
                    $from,
                    $to,
                    $refresh,
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
                        self::numWeeks($writingStreakStart, new DateTime())
                    );
                }

                unset($data['events']['check']);
                $data['writing_streak'] = $writingStreak;
                break;
            case 'topSubcategories':
                $events = self::filterEvents($events, $model->isPremium());

                $data = self::buildBreakdown(
                    $events,
                    $properties,
                    $filters,
                    'properties.response__data__subcategory',
                    $interval,
                    $from,
                    $to,
                    $refresh
                );

                foreach ($events as $event) {
                    if (!empty($data['events'][$event])) {
                        foreach ($data['events'][$event] as $subcategory => $counts) {
                            $key = str_replace(['advanced_', '_advanced'], ['', ''], $subcategory);
                            if (empty($subcategories[$key])) {
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
                                $category = $subcategories[$key];
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
                $events = self::filterEvents($events, $model->isPremium());

                $data = self::buildBreakdown(
                    $events,
                    $properties,
                    $filters,
                    'coalesce(properties.response__data__text, properties.response__data_text)',
                    $interval,
                    $from,
                    $to,
                    $refresh,
                    'ActionsBarValue',
                );
                break;
            default:
                return ['error' => 400, 'message' => "Unsupported chart type '$chart'"];
                break;
        }

        return $data;
    }
}
