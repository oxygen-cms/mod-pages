<?php

namespace OxygenModule\Pages\Repository;

use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Oxygen\Core\Templating\Templatable;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishes;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\SoftDeletes;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Data\Repository\QueryParameters;
use OxygenModule\Pages\Entity\Partial;

class DoctrinePartialRepository extends Repository implements PartialRepositoryInterface {

    use SoftDeletes, Versions, Publishes {
        Publishes::persist insteadof Versions;
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
     * @throws NoResultException
     * @return Partial
     */
    public function findByKey($key) {
        $q = $this->getQuery(
            $this->createSelectQuery()
                 ->andWhere('o.stage = :stage')
                 ->andWhere('o.key = :key')
                 ->setParameter('stage', Partial::STAGE_PUBLISHED)
                 ->setParameter('key', $key),
            new QueryParameters(['excludeTrashed', 'excludeVersions'])
        );

        try {
            return $q->getSingleResult();
        } catch(DoctrineNoResultException $e) {
            throw $this->makeNoResultException($e, $q);
        }
    }

    /**
     * @param string $key
     * @return Templatable|null
     */
    public function findByTemplateKey($key) {
        try {
            return $this->findByKey($key);
        } catch(NoResultException $e) {
            return null;
        }
    }
}