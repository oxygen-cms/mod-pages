<?php

namespace Oxygen\Pages\Repository;

use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishes;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\SoftDeletes;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Data\Repository\QueryParameters;
use Oxygen\Pages\Entity\Page;
use Oxygen\Pages\Entity\Partial;

class DoctrinePartialRepository extends Repository implements PartialRepositoryInterface {

    use SoftDeletes, Versions, Publishes {
        Publishes::persist insteadof Versions;
    }

    /**
     * The name of the entity.
     *
     * @var string
     */
    protected $entityName = 'Oxygen\Pages\Entity\Partial';

    /**
     * Finds a Partial based upon the key.
     *
     * @param string $key
     * @throws NoResultException
     * @return Partial
     */
    public function findByKey($key) {
        try {
            $qb = $this->getQuery(
                $this->createSelectQuery()
                    ->andWhere('o.stage = :stage')
                    ->andWhere('o.key = :key')
                    ->setParameter('stage', Partial::STAGE_PUBLISHED)
                    ->setParameter('key', $key),
                new QueryParameters(['excludeTrashed'])
            );
            return $qb->getSingleResult();
        } catch(DoctrineNoResultException $e) {
            throw new NoResultException($e, $this->replaceQueryParameters($qb->getDQL(), $qb->getParameters()));
        }
    }

}