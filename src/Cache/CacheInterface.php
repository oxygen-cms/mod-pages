<?php

namespace Oxygen\Pages\Cache;

interface CacheInterface {

    /**
     * Clears the cache for the page with the given slug.
     *
     * @param string $slug
     * @return void
     */
    public function clear($slug);

    /**
     * Clears the entire cache.
     *
     * @return void
     */
    public function clearAll();

    /**
     * Puts an item in the cache.
     *
     * @param string $slug
     * @param string $content
     * @return void
     */
    public function put($slug, $content);

}