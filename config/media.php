<?php

return [
    'unsplash' => [
        'access_key' => env('UNSPLASH_ACCESS_KEY', ''),
        'application_id' => env('UNSPLASH_APPLICATION_ID', ''),
        'secret_key' => env('UNSPLASH_SECRET_KEY', ''),
        'cache_ttl' => env('UNSPLASH_CACHE_TTL', 86400),
        'enabled' => env('UNSPLASH_ENABLED', false),
    ],
    'illustrations' => [
        'path' => public_path('assets/images/illustrations'),
        'cdn_prefix' => 'https://cdn.undraw.co/illustrations',
    ],
    'patterns' => [
        'topography' => [
            'fill' => '#14532d',
            'opacity' => 0.03,
        ],
        'hexagons' => [
            'fill' => '#14532d',
            'opacity' => 0.03,
        ],
        'circuit_board' => [
            'fill' => '#14532d',
            'opacity' => 0.02,
        ],
        'jigsaw' => [
            'fill' => '#14532d',
            'opacity' => 0.03,
        ],
        'camo_lite' => [
            'fill' => '#14532d',
            'opacity' => 0.02,
        ],
    ],
];
