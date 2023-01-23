<?php

return [
    'enabled' => env('HUBSPOT_ENABLED', false),
    'js_enabled' => env('HUBSPOT_JS_ENABLED', false),
    'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
    'hub_id' => env('HUBSPOT_HUB_ID', '24904016'),
    'form_id' => env('HUBSPOT_FORM_ID', "01dc84ed-dd08-4f21-a445-abb71e37cf0d"),
    'force_create_after' => env('HUBSPOT_FORCE_CREATE_AFTER', 20),
];
