<?php

use App\Models\LanguageGuidelines;

return [

    'currency' => 'USD',
    'plans' => [
        'witty_free' => [
            'price_id' => env('STRIPE_PRICE_WITTY_FREE'),
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
                'user_false_positives' => ['count' => 3],
                'user_term_replacements' => ['count' => 3],
                'organization_false_positives' => ['count' => 3],
                'organization_term_replacements' => ['count' => 3],
                'customer_success_helpcenter' => [],
            ],
        ],
        'witty_teams' => [
            'price_id' => env('STRIPE_PRICE_WITTY_TEAMS'),
            'price' => 178.80,
            'checkout' => true,
            'demo' => false,
            'features' => [
                'witty_free' => [],
                'invite_larger_teams' => [],
                'permissions' => [],
                'advanced_team_analytics' => [],
                'control_data_sharing' => [],
                'user_false_positives' => ['count' => 25],
                'user_term_replacements' => ['count' => 50],
                'organization_false_positives' => ['count' => 25],
                'organization_term_replacements' => ['count' => 50],
                'inclusion_nps_store' => [],
                'hr_add_on' => [],
                'unconscious_bias_video_training' => [],
                'customer_success_email' => [],
            ],
        ],
        'witty_enterprise' => [
            'price_id' => env('STRIPE_PRICE_WITTY_ENTERPRISE'),
            'price' => 240,
            'checkout' => false,
            'demo' => true,
            'features' => [
                'witty_teams' => [],
                'multiple_teams' => [],
                'advanced_organization_analytics' => [],
                'saml' => [],
                'private_cloud' => [],
                'user_false_positives' => ['count' => LanguageGuidelines::UNLIMITED],
                'user_term_replacements' => ['count' => LanguageGuidelines::UNLIMITED],
                'organization_false_positives' => ['count' => LanguageGuidelines::UNLIMITED],
                'organization_term_replacements' => ['count' => LanguageGuidelines::UNLIMITED],
                'custom_onboarding_training' => [],
                'customer_success_phone' => [],
            ],
        ],
    ],
];
