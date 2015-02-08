<?php

use Oxygen\Pages\Cache\CacheInterface;
use Oxygen\Pages\Entity\Page;
use Oxygen\Pages\Entity\Partial;

return [

    /*
    |--------------------------------------------------------------------------
    | Page Theme
    |--------------------------------------------------------------------------
    |
    | Specifies the view file that should be used to display pages.
    |
    */

    'theme' => 'yourTheme',

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Config information for caching pages.
    |
    */

    'cache' => [
        'enabled' => true,
        'location' => '/public/content/cache',
        'entities' => [
            'Oxygen\Pages\Entity\Page' => function(Page $page, CacheInterface $cache) {
                if($page->isPublished()) {
                    $cache->clear($page->getSlug());
                }
            },
            'Oxygen\Pages\Entity\Partial' => function(Partial $partial, CacheInterface $cache) {
                if($partial->isPublished()) {
                    $cache->clearAll();
                }
            }
        ]
    ]

];