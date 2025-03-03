<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Broadcasting
    |--------------------------------------------------------------------------
    */

    'broadcasting' => [
        'echo' => [
            'broadcaster' => 'pusher',
            'key' => env('VITE_PUSHER_APP_KEY'),
            'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
            'forceTLS' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Storage
    |--------------------------------------------------------------------------
    */

    'storage_disk' => env('FILAMENT_STORAGE_DISK', 'public'),
    'storage_path' => env('FILAMENT_STORAGE_PATH', 'uploads'),

    /*
    |--------------------------------------------------------------------------
    | Temporary File Upload
    |--------------------------------------------------------------------------
    */

    'temporary_file_upload' => [
        'disk' => env('FILAMENT_TEMPORARY_DISK', 'local'),
        'directory' => 'tmp',
        'expiry' => 120, // seconds
        'middleware' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default File Upload AppSettings
    |--------------------------------------------------------------------------
    */

    'default_file_upload' => [
        'disk' => 'public',
        'directory' => 'uploads',
        'visibility' => 'public',
        'preserve_filenames' => true,
        'max_size' => 10240, // 10MB
        'accepted_file_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Assets Path
    |--------------------------------------------------------------------------
    */

    'assets_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Livewire Loading Delay
    |--------------------------------------------------------------------------
    */

//    'livewire_loading_delay' => 300, // milliseconds

    'livewire' => [
        'loading_delay' => false, // Disable loading delay
    ],
];
