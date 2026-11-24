<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CargoTrace Configuration & Delay Thresholds
    |--------------------------------------------------------------------------
    |
    | Group Scenario & Deterministic Delay Rules (UAS Brief page 8 & 23)
    | Configurable delay thresholds in hours:
    | - LOW: > 0 hours and <= low_threshold
    | - MEDIUM: > low_threshold and <= medium_threshold
    | - HIGH: > medium_threshold
    |
    */
    'delay_thresholds' => [
        'low' => (int) env('DELAY_THRESHOLD_LOW', 6),       // 0 - 6 hours
        'medium' => (int) env('DELAY_THRESHOLD_MEDIUM', 24), // 6 - 24 hours
        // > 24 hours = HIGH
    ],

    /*
    | Group scenario assignment (e.g., 'Group A', 'Group B', 'Group C')
    */
    'group_scenario' => env('GROUP_SCENARIO', 'Group A'),

    /*
    | Weather API Configuration (Open-Meteo free API - no key needed)
    */
    'weather_api_url' => env('WEATHER_API_URL', 'https://api.open-meteo.com/v1/forecast'),
    'weather_cache_ttl' => (int) env('WEATHER_CACHE_TTL', 3600), // 1 hour
];
