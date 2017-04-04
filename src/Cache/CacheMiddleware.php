<?php

namespace OxygenModule\Pages\Cache;

use Closure;
use Oxygen\Preferences\PreferencesManager;

class CacheMiddleware {

    /**
     * Constructs the CacheFilter.
     *
     * @param CacheInterface                         $cache
     * @param \Oxygen\Preferences\PreferencesManager $preferences
     */
    public function __construct(CacheInterface $cache, PreferencesManager $preferences) {
        $this->cache = $cache;
        $this->preferences = $preferences;
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

        if($this->preferences->get('modules.pages::cache.enabled') === true) {
            $this->cache->put($request->path(), $response->getContent());
        }

        return $response;
    }

}
