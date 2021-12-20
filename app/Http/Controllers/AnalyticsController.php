<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $apiKey = config('posthog.api_key');
        $apiKey = 'phc_tmJbApENFHLXMjwG1hHMYO4Md8qR4XAGRforELIiDwp';

        $interval = 'week';

        $baseUrl = config('posthog.host') . "/api/insight/trend?token=$apiKey&interval=$interval";

        $id = '70754244879261828598149245075925275983200575447385953845348364073429468308144';
        $property = 'distinct_id';

        //$id = 'dashboard:0'; $property = '$group_1';

        $properties = [
            [
                'key' => $property,
                'value' => $id,
                'operator' => 'exact',
                'type' => 'person',
            ]
        ];

        $eventFilter = [
            'type' => 'events',
            'properties' => $properties
        ];

        $data = [];
        foreach (['check', 'alternative', 'ignore'] as $event) {
            $eventFilter['id'] = $eventFilter['name'] = $event;

            // [{"id":"$pageview","name":"$pageview","type":"events","order":0,"properties":[{"key":"distinct_id","value":["48vHN2rW28SvzA6D4NdgHajLOjBP1ulsI0Rr5DNas4i"],"operator":"exact","type":"person"}]}]


            $response = Http::withToken(config('posthog.personal_api_key'))
                ->get($baseUrl . '&events=[' . json_encode($eventFilter) . ']');

            $response = $response->collect()->all();
            if (isset($response['result'][0])) {
                $data[$event] = array_combine($response['result'][0]['days'], $response['result'][0]['data']);
            }
        }

        return view('analytics', [
            'data' => $data,
        ]);
    }
}
