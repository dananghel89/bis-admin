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
    | Map
    |--------------------------------------------------------------------------
    |
    | Where the property maps open when there is nothing to show yet, and the
    | area the demo seeders place their properties in. Barcelona by default.
    |
    */

    'map_center' => [
        'lat' => (float) env('MAP_CENTER_LAT', 41.3874),
        'lng' => (float) env('MAP_CENTER_LNG', 2.1686),
        'zoom' => (int) env('MAP_ZOOM', 13),
    ],

];
