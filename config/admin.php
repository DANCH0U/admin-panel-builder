<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default panel 
    |--------------------------------------------------------------------------
    |
    | Default admin panel is different for each user .
    |
    */


    /*
    |--------------------------------------------------------------------------
    | Languages (shared across panels)
    |--------------------------------------------------------------------------
    |
    | label, locale, font (Google Fonts CSS embed URL).
    |
    */
    'languages' => [
        [
            'label' => 'English',
            'locale' => 'en',
            'family' => 'Plus Jakarta Sans',
            'font' => 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400..700;1,400..700&display=swap',
        ],
        [
            'label' => 'Arabic',
            'locale' => 'ar',
            'family' => 'Cairo',
            'font' => 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap',
        ],
    ],

    'default_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | UI
    |--------------------------------------------------------------------------
    */
    'ui' => [
        /*
         * Delay (ms) before the panel navigation loader appears.
         * Fast requests under this threshold feel instant (no flash).
         */
        'loading_delay_ms' => 200,
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size_kb' => 4096,
        'image_mimes' => 'jpeg,jpg,png,webp,avif',
        'disk' => 'public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Grid / Tables
    |--------------------------------------------------------------------------
    */
    'table' => [
        'default_per_page' => 25,
        'per_page_options' => [10, 25, 50, 100],
    ],
];
