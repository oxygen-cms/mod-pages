<?php

namespace Oxygen\Pages\Cache;

class CacheFilter {

    /**
     * Constructs the CacheFilter.
     *
     * @param CacheInterface $cache
     */

    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * The CacheFilter creates cached versions of pages.
     *
     * @param $route
     * @param $request
     * @param $response
     */

    public function filter($route, $request, $response) {
        $this->cache->put($request->path(), $response->getContent());
    }

}