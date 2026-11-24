<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site
    |--------------------------------------------------------------------------
    |
    | Shown in page titles, headers and the landing page. The contact details
    | are shown on the landing page; the email also receives the contact form
    | messages.
    |
    */

    'site_title' => env('SITE_TITLE', 'BIS'),
    'tagline' => env('SITE_TAGLINE', 'Architecture Web Application'),

    'contact_email' => env('CONTACT_EMAIL', 'contact@example.com'),
    'contact_address' => env('CONTACT_ADDRESS'),
    'contact_phone' => env('CONTACT_PHONE'),

    /*
    |--------------------------------------------------------------------------
    | First admin account
    |--------------------------------------------------------------------------
    |
    | Created by `php artisan db:seed` (database/seeders/AdminUserSeeder.php) when no
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
