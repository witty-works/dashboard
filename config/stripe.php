<?php

return [

    'plans' => [
        'witty_free' => [
            'price_id' => env('STRIPE_PRICE_WITTY_ME'),
            'price' => 0,
            'checkout' => false,
            'features' => [
                'inclusive_writing' => [],
                'spelling_grammar' => [],
                'browser_plugin' => [],
                'sso' => [],
                'organization_guidelines' => [],
                'basic_team_analytics' => [],
                'invite_smaller_teams' => ['count' => 3],
                'false_positives' => ['count' => 5],
                'term_replacements' => ['count' => 10],
                'customer_success_helpcenter' => [],
            ],
        ],
        'witty_teams' => [
            'price_id' => env('STRIPE_PRICE_WITTY_TEAMS'),
            'checkout' => true,
            'demo' => false,
            'features' => [
                'witty_free' => [],
                'invite_larger_teams' => [],
                'permissions' => [],
                'advanced_team_analytics' => [],
                'control_data_sharing' => [],
                'false_positives' => ['count' => 10],
                'term_replacements' => ['count' => 50],
                'inclusion_nps_store' => [],
                'hr_add_on' => [],
                'unconscious_bias_video_training' => [],
                'customer_success_email' => [],
            ],
        ],
        'witty_enterprise' => [
            'price_id' => env('STRIPE_PRICE_WITTY_ENTERPRISE'),
            'price' => 850,
            'checkout' => false,
            'demo' => true,
            'features' => [
                'witty_teams' => [],
                'multiple_teams' => [],
                'advanced_organization_analytics' => [],
                'saml' => [],
                'private_cloud' => [],
                'false_positives' => ['count' => 999],
                'term_replacements' => ['count' => 999],
                'custom_onboarding_training' => [],
                'customer_success_phone' => [],
            ],
        ],
    ],
];
