<?php

return [
    'default_provider' => env('AI_PROVIDER', 'openai'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 4096),
        'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'embedding-001'),
        'max_tokens' => (int) env('GEMINI_MAX_TOKENS', 8192),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.7),
    ],

    'api_service' => [
        'url' => env('AI_SERVICE_URL', 'http://localhost:8000/api/v1'),
        'api_key' => env('AI_SERVICE_TOKEN'),
        'timeout' => (int) env('AI_SERVICE_TIMEOUT', 120),
    ],

    'providers' => [
        'openai' => [
            'class' => \App\Services\Ai\Providers\OpenAiProvider::class,
        ],
        'api' => [
            'class' => \App\Services\Ai\Providers\ApiAiProvider::class,
        ],
        'gemini' => [
            'class' => \App\Services\Ai\Providers\GeminiProvider::class,
        ],
        'google' => [
            'class' => \App\Services\Ai\Providers\GeminiProvider::class,
        ],
        'fallback' => [
            'class' => \App\Services\Ai\Providers\FallbackProvider::class,
        ],
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
