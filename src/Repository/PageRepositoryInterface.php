<?php

namespace OxygenModule\Pages\Repository;

use Oxygen\Core\Templating\TemplatableRepositoryInterface;
use Oxygen\Data\Repository\RepositoryInterface;
use OxygenModule\Pages\Entity\Page;

interface PageRepositoryInterface extends RepositoryInterface, TemplatableRepositoryInterface {

    /**
     * Finds a Page based upon the slug.
     *
     * @param string $slug
     * @return Page
     */
    public function findBySlug($slug);

}