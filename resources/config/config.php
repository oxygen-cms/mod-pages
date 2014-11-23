<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Page Theme
    |--------------------------------------------------------------------------
    |
    | Specifies the view file that should be used to display pages.
    |
    */

    'theme' => 'oxygen/pages::pages.view',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Config information for caching pages.
    |
    */

    'cache' => [
        'enabled' => false,
        'location' => '/public/content/cache'
    ]

];