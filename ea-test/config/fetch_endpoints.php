<?php

return [
    'endpoints' => [
        'stocks' => [
            'url' => env('API_HOST_URL') . '/api/stocks',
            'params' => [
                'dateFrom' => now()->format('Y-m-d'),
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 500,
            ],
        ],
        'incomes' => [
            'url' => env('API_HOST_URL') . '/api/incomes',
            'params' => [
                'dateFrom' => '2024-01-01',
                'dateTo' => '2026-01-01',
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 500,
            ],
        ],
        'sales' => [
            'url' => env('API_HOST_URL') . '/api/sales',
            'params' => [
                'dateFrom' => '2024-01-01',
                'dateTo' => '2026-01-01',
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 500,
            ],
        ],
    ]
];
