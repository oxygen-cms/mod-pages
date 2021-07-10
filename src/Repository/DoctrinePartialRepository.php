<?php

namespace OxygenModule\Pages\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Oxygen\Core\Templating\Templatable;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishes;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Data\Repository\ExcludeTrashedScope;
use Oxygen\Data\Repository\ExcludeVersionsScope;
use Oxygen\Data\Repository\QueryParameters;
use OxygenModule\Pages\Entity\Partial;

class DoctrinePartialRepository extends Repository implements PartialRepositoryInterface {

    use Versions, Publishes {
        Publishes::onEntityPersisted insteadof Versions;
    }

    /**
     * The name of the entity.
     *
     * @var string
     */
    protected $entityName = Partial::class;

    /**
     * Finds a Partial based upon the key.
     *
     * @param string $key
     * @param bool $onlyPublished
     * @return Partial
     * @throws NonUniqueResultException
     */
    public function findByKey($key, $onlyPublished = true) {
        $qb = $this->createSelectQuery()
            ->andWhere('o.key = :key')
            ->setParameter('key', $key);

        $clauses = [new ExcludeTrashedScope()];
        if($onlyPublished) {
            $qb->andWhere('o.stage = :stage')
                ->setParameter('stage', Partial::STAGE_PUBLISHED);
        } else {
            $clauses[] = new ExcludeVersionsScope();
        }

        $q = $this->getQuery(
            $qb,
            new QueryParameters($clauses)
        );

        try {
            return $q->getSingleResult();
        } catch(DoctrineNoResultException $e) {
            throw $this->makeNoResultException($e, $q);
        }
    }

    /**
     * @param string $key
     * @param bool $onlyPublished
     * @return Templatable|null
     * @throws NonUniqueResultException
     */
    public function findByTemplateKey($key, $onlyPublished = true) {
        try {
            return $this->findByKey($key, $onlyPublished);
        } catch(NoResultException $e) {
            return null;
        }
    }
}
