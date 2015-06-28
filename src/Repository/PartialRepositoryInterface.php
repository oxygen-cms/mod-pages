<?php

namespace OxygenModule\Pages\Repository;

use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\RepositoryInterface;
use OxygenModule\Pages\Entity\Partial;

interface PartialRepositoryInterface extends RepositoryInterface {

    /**
     * Finds a Partial based upon the key.
     *
     * @param string $key
     * @throws NoResultException
     * @return Partial
     */
    public function findByKey($key);

}