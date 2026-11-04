<?php

return [

    /*
    |--------------------------------------------------------------------------
    | First admin account
    |--------------------------------------------------------------------------
    |
    | Created by `php artisan db:seed` (database/seeds/AdminUserSeeder.php) when no
    | user with this email exists yet. Set real values in .env, never in the repo,
    | and always set SEED_ADMIN_PASSWORD on a public server.
    |
    */

    'seed_admin' => [
        'name' => env('SEED_ADMIN_NAME', 'Admin'),
        'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
        'password' => env('SEED_ADMIN_PASSWORD', 'password'),
    ],

];
