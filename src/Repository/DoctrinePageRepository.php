<?php

namespace Oxygen\Pages\Repository;

use Exception;
use \Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishes;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\SoftDeletes;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Data\Repository\QueryParameters;
use Oxygen\Pages\Entity\Page;

class DoctrinePageRepository extends Repository implements PageRepositoryInterface {

    use SoftDeletes, Versions, Publishes {
        Publishes::persist insteadof Versions;
    }

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = 'Oxygen\Pages\Entity\Page';

    /**
     * Finds a Page based upon the slug.
     *
     * @param string $slug
     * @throws NoResultException
     * @return Page
     */
    public function findBySlug($slug) {
        try {
            $qb = $this->getQuery(
                $this->createSelectQuery()
                    ->andWhere('o.stage = :stage')
                    ->andWhere('o.slug = :slug')
                    ->setParameter('stage', Page::STAGE_PUBLISHED)
                    ->setParameter('slug', $slug),
                new QueryParameters(['excludeTrashed'])
            );

            return $qb->getSingleResult();
        } catch(DoctrineNoResultException $e) {
            throw new NoResultException($e, $this->replaceQueryParameters($qb->getDQL(), $qb->getParameters()));
        }
    }

}