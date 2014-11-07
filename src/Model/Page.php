<?php

namespace Oxygen\Pages\Model;

use Oxygen\Core\Model\Resource;
use Oxygen\Core\Model\SoftDeleting\SoftDeletingTrait;
use Oxygen\Core\Model\Versionable\VersionableTrait;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class Page extends Resource {

    use SoftDeletingTrait, VersionableTrait;

    const STAGE_DRAFT = 0;
    const STAGE_PENDING_REVIEW = 1;
    const STAGE_PUBLISHED = 2;
    const STAGE_ARCHIVED = 3;

    /**
     * Determines if the page is published.
     *
     * @return boolean
     */

    public function published() {
        return $this->stage == self::STAGE_PUBLISHED;
    }

}