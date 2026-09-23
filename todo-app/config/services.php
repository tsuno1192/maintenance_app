<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service-to-service API token
    |--------------------------------------------------------------------------
    |
    | Shared secret used by hoikuku-app when calling todo-app task APIs.
    | Send ONLY as: Authorization: Bearer <token>
    | Production requires a token of 32+ characters (not a placeholder).
    |
    */
    'api_token' => env('TODO_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Action List UI access token
    |--------------------------------------------------------------------------
    |
    | Optional separate token for the browser UI. Falls back to api_token.
    |
    */
    'ui_token' => env('ACTION_LIST_UI_TOKEN'),

];
