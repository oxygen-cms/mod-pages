<?php

namespace OxygenModule\Pages\Cache;

use Closure;
use Illuminate\Http\Request;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;

class CacheMiddleware {
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * Constructs the CacheFilter.
     *
     * @param CacheInterface                         $cache
     * @param PreferencesManager $preferences
     */
    public function __construct(CacheInterface $cache, PreferencesManager $preferences) {
        $this->cache = $cache;
        $this->preferences = $preferences;
    }

    /**
     * Run the request filter.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws PreferenceNotFoundException
     */
    public function handle($request, Closure $next) {
        $response = $next($request);

        if($this->preferences->get('modules.pages::cache.enabled') === true) {
            $this->cache->put($request->path(), $response->getContent());
        }

        return $response;
    }

}
