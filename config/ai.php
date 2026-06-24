<?php

return [
    'default_provider' => env('AI_PROVIDER', 'openai'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 4096),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
    ],

    'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),

    'rate_limit' => [
        'per_minute' => 10,
        'per_user_per_day' => 100,
    ],

    'budget' => [
        'monthly_limit' => 500,
        'alert_threshold' => 0.8,
    ],

    'features_enabled' => [
        'basic' => false,
        'pro' => true,
        'enterprise' => true,
    ],
];
