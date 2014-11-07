<?php

namespace Oxygen\Pages\Repository;

use Exception;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishable;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\SoftDeletes;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Pages\Entity\Page;

class DoctrinePartialRepository extends Repository implements PartialRepositoryInterface {

    use SoftDeletes, Versions, Publishable {
        Publishable::persist insteadof Versions;
    }

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = 'Oxygen\Pages\Entity\Partial';

}