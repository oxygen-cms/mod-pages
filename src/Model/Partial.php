<?php

namespace Oxygen\Pages\Model;

use Oxygen\Core\Model\Resource;
use Oxygen\Core\Model\SoftDeleting\SoftDeletingTrait;
use Oxygen\Core\Model\Versionable\VersionableTrait;

class Partial extends Resource {

    use SoftDeletingTrait, VersionableTrait;

    /**
     * Retrieves a partial by its key.
     *
     * @param string $key
     * @return Partial
     */

    public static function get($key) {
        return (new static())->newQuery()->where('key', $key)->firstOrFail();
    }

}