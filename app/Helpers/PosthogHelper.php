<?php

namespace App\Helpers;

use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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

    public static function getUrl()
    {
        $projectId = config('posthog.project_id');
        return config('posthog.host') . "/api/projects/$projectId/insights/trend";
    }

    public static function fetchData($filter, $url, $refresh = false)
    {
        $key = 'posthog:' . md5($url) . ':' . md5(serialize($filter));
        if ($refresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, config('posthog.insights_cache_time'), function () use ($url, $filter) {
            $response = Http::withToken(config('posthog.personal_api_key'))
                ->post($url, $filter);

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
