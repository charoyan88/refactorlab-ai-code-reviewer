<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains the OpenAI API configuration details.
    |
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
        'temperature' => env('OPENAI_TEMPERATURE', 0.1),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains the GitHub API configuration details.
    |
    */
    'github' => [
        'token' => env('GITHUB_API_TOKEN'),
        'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Filters
    |--------------------------------------------------------------------------
    |
    | Defines which files to include or exclude from the code review process.
    |
    */
    'file_filters' => [
        'include' => [
            '*.php',
            '*.js',
            '*.ts',
            '*.py',
            '*.css',
            '*.scss',
        ],
        'exclude' => [
            'vendor/*',
            'node_modules/*',
            '*.min.js',
            '*.min.css',
            '*.lock',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Review Settings
    |--------------------------------------------------------------------------
    |
    | General settings for the review process.
    |
    */
    'settings' => [
        'max_files_per_review' => env('MAX_FILES_PER_REVIEW', 10),
        'max_diff_size' => env('MAX_DIFF_SIZE', 5000), // in lines
        'comment_threshold' => env('COMMENT_THRESHOLD', 0.7), // confidence threshold
        'batch_processing' => env('BATCH_PROCESSING', true),
    ],
]; 