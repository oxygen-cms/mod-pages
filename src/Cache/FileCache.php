<?php

namespace Oxygen\Pages\Cache;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;

class FileCache implements CacheInterface {

    /**
     * Constructs the FileCache
     *
     * @param string     $location
     * @param Filesystem $files
     */

    public function __construct($location, Filesystem $files) {
        $this->location = $location;
        $this->files = $files;
    }

    /**
     * Clears the cache for the page with the given slug.
     *
     * @param string $slug
     * @return void
     */
    public function clear($slug) {
        $file = $this->location . '/' . $slug . '.html';

        if($this->files->exists($file)) {
            $this->files->delete($file);
        }
    }

    /**
     * Clears the entire cache.
     *
     * @return void
     */

    public function clearAll() {
        if($this->files->exists($this->location)) {
            $this->files->deleteDirectory($this->location);
        }
    }

    /**
     * Puts an item in the cache.
     *
     * @param string $slug
     * @param string $content
     * @return void
     */

    public function put($slug, $content) {
        $file = $this->location . '/' . $slug . '.html';
        $dir = dirname($file);
        if(!$this->files->exists($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }
        $this->files->put($file, $content . "\n" . '<!-- Cached at ' . Carbon::now() . ' -->' . "\n\n");
    }
}