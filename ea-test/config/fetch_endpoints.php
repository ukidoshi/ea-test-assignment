<?php

return [
    'endpoints' => [
        'stocks' => [
            'url' => env('API_HOST_URL') . '/api/stocks',
            'params' => [
                'dateFrom' => now()->format('Y-m-d'),
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 100,
            ],
        ],
        'incomes' => [
            'url' => env('API_HOST_URL') . '/api/incomes',
            'params' => [
                'dateFrom' => '2024-01-01',
                'dateTo' => '2026-01-01',
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 100,
            ],
        ],
        'sales' => [
            'url' => env('API_HOST_URL') . '/api/sales',
            'params' => [
                'dateFrom' => '2024-01-01',
                'dateTo' => '2026-01-01',
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 100,
            ],
        ],
        'orders' => [
            'url' => env('API_HOST_URL') . '/api/orders',
            'params' => [
                'dateFrom' => '2024-01-01',
                'dateTo' => '2026-01-01',
                'key' => env('API_KEY'),
                'page' => 1,
                'limit' => 100,
            ],
        ]
    ]
];
