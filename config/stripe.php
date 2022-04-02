<?php

return [

    'plans' => [
        'witty_me' => [
            'price_id' => env('STRIPE_PRICE_WITTY_ME'),
            'checkout' => false,
            'features' => [
                'inclusive_writing' => [],
                'spelling_grammar' => [],
                'browser_plugin' => [],
                'sso' => [],
                'customer_success_helpcenter' => [],
                'organization_guidelines' => [],
                'basic_team_analytics' => [],
                'invite_smaller_teams' => ['count' => 3],
                'false_positives' => ['count' => 5],
                'term_replacements' => ['count' => 10],
            ],
        ],
        'witty_teams' => [
            'price_id' => env('STRIPE_PRICE_WITTY_TEAMS'),
            'checkout' => true,
            'demo' => true,
            'features' => [
                'witty_me' => [],
                'invite_larger_teams' => [],
                'advanced_team_analytics' => [],
                'customer_success_email' => [],
                'false_positives' => ['count' => 10],
                'term_replacements' => ['count' => 50],
            ],
        ],
        'witty_enterprise' => [
            'price_id' => env('STRIPE_PRICE_WITTY_ENTERPRISE'),
            'checkout' => false,
            'demo' => true,
            'features' => [
                'witty_teams' => [],
                'multiple_teams' => [],
                'advanced_organization_analytics' => [],
                'customer_success_phone' => [],
                'saml' => [],
                'private_cloud' => [],
                'false_positives' => ['count' => 999],
                'term_replacements' => ['count' => 999],
            ],
        ],
    ],
];
