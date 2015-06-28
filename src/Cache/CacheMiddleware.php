<?php

namespace OxygenModule\Pages\Cache;

use Closure;

class CacheMiddleware {

    /**
     * Constructs the CacheFilter.
     *
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request                       $request
     * @param \Closure                                       $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $response = $next($request);

        $this->cache->put($request->path(), $response->getContent());

        return $response;
    }

}