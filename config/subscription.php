<?php

return [
    'plans' => [
        'basic' => [
            'name' => 'Basic',
            'price_monthly' => 0,
            'ai_features' => false,
            'sms' => false,
            'priority_support' => false,
            'ai_usage_limit' => 0,
        ],
        'pro' => [
            'name' => 'Pro',
            'price_monthly' => 29.99,
            'ai_features' => true,
            'sms' => true,
            'priority_support' => false,
            'ai_usage_limit' => 500,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price_monthly' => 99.99,
            'ai_features' => true,
            'sms' => true,
            'priority_support' => true,
            'ai_usage_limit' => 5000,
        ],
    ],

    'features' => [
        'ai_eligibility_check' => ['pro', 'enterprise'],
        'ai_document_analysis' => ['pro', 'enterprise'],
        'ai_candidate_ranking' => ['enterprise'],
        'ai_chatbot' => ['pro', 'enterprise'],
        'ai_insights_dashboard' => ['enterprise'],
        'ai_report_generation' => ['pro', 'enterprise'],
        'sms_notifications' => ['pro', 'enterprise'],
        'priority_support' => ['enterprise'],
        'bulk_operations' => ['enterprise'],
        'api_access' => ['basic', 'pro', 'enterprise'],
    ],
];
