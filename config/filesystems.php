<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('STORAGE_URL', '/storage'),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('SUPABASE_S3_KEY', env('SUPABASE_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID'))),
            'secret' => env('SUPABASE_S3_SECRET', env('SUPABASE_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY'))),
            'region' => env('SUPABASE_S3_REGION', env('SUPABASE_DEFAULT_REGION', env('AWS_DEFAULT_REGION', 'us-east-1'))),
            'bucket' => env('SUPABASE_S3_BUCKET', env('SUPABASE_BUCKET', env('AWS_BUCKET'))),
            'url' => env('SUPABASE_S3_URL', env('SUPABASE_URL', env('AWS_URL'))),
            'endpoint' => env('SUPABASE_S3_ENDPOINT', env('SUPABASE_ENDPOINT', env('AWS_ENDPOINT'))),
            'use_path_style_endpoint' => env('SUPABASE_S3_USE_PATH_STYLE', env('SUPABASE_USE_PATH_STYLE_ENDPOINT', env('AWS_USE_PATH_STYLE_ENDPOINT', true))),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
