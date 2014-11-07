<?php

namespace Oxygen\Pages\Repository;

use Oxygen\Data\Repository\RepositoryInterface;
use Oxygen\Pages\Entity\Page;

interface PageRepositoryInterface extends RepositoryInterface {

    /**
     * Finds a Page based upon the slug.
     *
     * @param string $slug
     * @return Page
     */

    public function findBySlug($slug);

}