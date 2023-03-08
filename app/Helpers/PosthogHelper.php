<?php

namespace App\Helpers;

use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

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

    public static $posthog_reset = false;

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
    }

    public static function getUserFilter(User $user)
    {
        $postHogId = config('posthog.dashboard_user_id_override');
        if (empty($postHogId)) {
            $postHogId = $user->posthogId();
        }

        return [
            'type' => 'AND',
            'values' => [
                [
                    'key' => 'dashboard_id',
                    'value' => $postHogId,
                    'operator' => 'exact',
                    'type' => 'person',
                ]
            ]
        ];
    }
}
