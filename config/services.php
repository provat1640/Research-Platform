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

    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'key' => env('SUPABASE_SERVICE_KEY'),
        'timeout' => env('SUPABASE_TIMEOUT', 10),
        'connect_timeout' => env('SUPABASE_CONNECT_TIMEOUT', 3),
    ],

    'ai' => [
        'base_url' => env(
            'AI_BASE_URL',
            env('GROQ_API_KEY') ? env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1') : 'http://127.0.0.1:11434/v1'
        ),
        'key' => env('AI_API_KEY', env('GROQ_API_KEY')),
        'model' => env('AI_MODEL', env('GROQ_MODEL', env('GROQ_DEFAULT_MODEL', 'llama-3.3-70b-versatile'))),
        'timeout' => env('AI_TIMEOUT', 30),
        'connect_timeout' => env('AI_CONNECT_TIMEOUT', 5),
        'temperature' => env('AI_TEMPERATURE', 0.2),
    ],

    'groq' => [
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'free_models' => [
            'llama-3.3-70b-versatile' => 'Llama 3.3 70B',
            'deepseek-r1-distill-70b' => 'DeepSeek-R1 Distill 70B',
            'qwen/qwen3-32b' => 'Qwen 2.5/3 32B',
        ],
    ],

    'mcp' => [
        'access_token' => env('MCP_ACCESS_TOKEN'),
    ],

];
