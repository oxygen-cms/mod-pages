<?php

namespace Oxygen\Pages\Repository;

use Exception;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishable;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\SoftDeletes;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Pages\Entity\Page;

class DoctrinePageRepository extends Repository implements PageRepositoryInterface {

    use SoftDeletes, Versions, Publishable {
        Publishable::persist insteadof Versions;
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
            return $this->createScopedQueryBuilder(['excludeTrashed'])
                ->andWhere('o.stage = :stage')
                ->andWhere('o.slug = :slug')
                ->setParameter('stage', Page::STAGE_PUBLISHED)
                ->setParameter('slug', $slug)
                ->getQuery()
                ->getSingleResult();
        } catch(Exception $e) {
            throw new NoResultException($e);
        }
    }

}