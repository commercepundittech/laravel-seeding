<?php
return [
    /*
   |--------------------------------------------------------------------------
   | LaravelSeeding Settings
   |--------------------------------------------------------------------------
   |
   | LaravelSeeding is disabled by default, when enabled is set to true in app.php.
   | You can override the value by setting enable to true or false instead of null.
   |
   */
    'enabled' => env('COMMERCEPUNDITTECH_LARAVEL_SEEING_ENABLED', false),
    'table_names' => [
        'seeder' => 'laravel_seeder',
    ]

];