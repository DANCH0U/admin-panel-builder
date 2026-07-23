<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default panel key
    |--------------------------------------------------------------------------
    */
    'default' => env('ADMIN_DEFAULT_PANEL', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Panels
    |--------------------------------------------------------------------------
    |
    | Each panel has its own URL prefix, middleware, menu class, and
    | panel_settings DB row (branding). Add another entry to create a
    | second panel (e.g. vendor) with its own routes + menu.
    |
    */
    'panels' => [

        'admin' => [
            'name' => env('ADMIN_NAME', 'Admin Panel'),
            'prefix' => env('ADMIN_PREFIX', 'admin'),
            'middleware' => ['auth', 'admin', 'panel:admin'],
            'menu' => \App\AdminPanel\Menus\AdminMenu::class,
            'auth' => [
                'login_route' => 'login',
                'home' => null,
            ],
            'ui' => [
                'logo_url' => env('ADMIN_LOGO_URL', '/admin-logo.svg'),
                'navbar_title' => env('ADMIN_NAVBAR_TITLE', 'Admin Panel'),
                'show_theme_toggle' => true,
            ],
        ],

        // Example second panel (uncomment + add routes/middleware to enable):
        // 'vendor' => [
        //     'name' => 'Vendor Panel',
        //     'prefix' => 'vendor',
        //     'middleware' => ['auth', 'panel:vendor'],
        //     'menu' => \App\AdminPanel\Menus\VendorMenu::class,
        //     'auth' => ['login_route' => 'login', 'home' => null],
        //     'ui' => ['logo_url' => null, 'show_theme_toggle' => true],
        // ],

    ],

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

    'default_locale' => env('ADMIN_LOCALE', env('APP_LOCALE', 'en')),

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size_kb' => (int) env('ADMIN_MAX_UPLOAD_KB', 4096),
        'image_mimes' => env('ADMIN_IMAGE_MIMES', 'jpeg,jpg,png,webp,avif'),
        'disk' => env('ADMIN_UPLOAD_DISK', 'public'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Grid / Tables
    |--------------------------------------------------------------------------
    */
    'table' => [
        'default_per_page' => (int) env('ADMIN_TABLE_PER_PAGE', 25),
        'per_page_options' => [10, 25, 50, 100],
        'tab_counts' => (bool) env('ADMIN_TABLE_TAB_COUNTS', false),
        'tab_count_ttl' => (int) env('ADMIN_TABLE_TAB_COUNT_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backward-compatible aliases
    |--------------------------------------------------------------------------
    */
    'name' => env('ADMIN_NAME', 'Admin Panel'),
    'prefix' => env('ADMIN_PREFIX', 'admin'),
    'admin_route_prefix' => env('ADMIN_PREFIX', 'admin'),
    'max_upload_size' => (int) env('ADMIN_MAX_UPLOAD_KB', 4096),
    'supported_image_mimes' => env('ADMIN_IMAGE_MIMES', 'jpeg,jpg,png,webp,avif'),

];
