<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TMQ 通知設定
    |--------------------------------------------------------------------------
    |
    | ワークフロー移行時の通知先です。
    | - mail: 宛先メールアドレス（カンマ区切り可）
    | - teams_webhook: Microsoft Teams Incoming Webhook URL
    |
    | ステップ固有の値が空の場合は default を使用します。
    |
    */

    'notifications' => [
        'enabled' => env('TMQ_NOTIFICATIONS_ENABLED', true),

        'default' => [
            'mail' => env('TMQ_NOTIFY_DEFAULT_MAIL', 'tmq-notify@example.com'),
            'teams_webhook' => env('TMQ_NOTIFY_DEFAULT_TEAMS_WEBHOOK'),
        ],

        'trouble' => [
            'leader' => [
                'mail' => env('TMQ_NOTIFY_TROUBLE_LEADER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_TROUBLE_LEADER_TEAMS_WEBHOOK'),
            ],
            'ops_manager' => [
                'mail' => env('TMQ_NOTIFY_TROUBLE_OPS_MANAGER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_TROUBLE_OPS_MANAGER_TEAMS_WEBHOOK'),
            ],
            'maintenance_leader' => [
                'mail' => env('TMQ_NOTIFY_TROUBLE_MAINT_LEADER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_TROUBLE_MAINT_LEADER_TEAMS_WEBHOOK'),
            ],
            'maintenance_manager' => [
                'mail' => env('TMQ_NOTIFY_TROUBLE_MAINT_MANAGER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_TROUBLE_MAINT_MANAGER_TEAMS_WEBHOOK'),
            ],
            'completed' => [
                'mail' => env('TMQ_NOTIFY_TROUBLE_COMPLETED_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_TROUBLE_COMPLETED_TEAMS_WEBHOOK'),
            ],
        ],

        'repair_report' => [
            'leader' => [
                'mail' => env('TMQ_NOTIFY_REPAIR_LEADER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_REPAIR_LEADER_TEAMS_WEBHOOK'),
            ],
            'manager' => [
                'mail' => env('TMQ_NOTIFY_REPAIR_MANAGER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_REPAIR_MANAGER_TEAMS_WEBHOOK'),
            ],
            'ops_leader' => [
                'mail' => env('TMQ_NOTIFY_REPAIR_OPS_LEADER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_REPAIR_OPS_LEADER_TEAMS_WEBHOOK'),
            ],
            'ops_manager' => [
                'mail' => env('TMQ_NOTIFY_REPAIR_OPS_MANAGER_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_REPAIR_OPS_MANAGER_TEAMS_WEBHOOK'),
            ],
            'completed' => [
                'mail' => env('TMQ_NOTIFY_REPAIR_COMPLETED_MAIL'),
                'teams_webhook' => env('TMQ_NOTIFY_REPAIR_COMPLETED_TEAMS_WEBHOOK'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Python Engine
    |--------------------------------------------------------------------------
    */
    'python_engine' => [
        'url' => env('PYTHON_ENGINE_URL', 'http://python_engine:8000'),
        'timeout' => (int) env('PYTHON_ENGINE_TIMEOUT', 30),
        'default_cycle_days' => (int) env('TMQ_DEFAULT_CYCLE_DAYS', 180),
        'min_cycle_days' => (int) env('TMQ_MIN_CYCLE_DAYS', 7),
        'max_cycle_days' => (int) env('TMQ_MAX_CYCLE_DAYS', 365),
    ],
];
