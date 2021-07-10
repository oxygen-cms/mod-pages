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
use OxygenModule\Pages\Entity\Page;

class DoctrinePageRepository extends Repository implements PageRepositoryInterface {

    use Versions, Publishes {
        Publishes::onEntityPersisted insteadof Versions;
    }

    /**
     * The name of the entity.
     *
     * @var string
     */

    protected $entityName = Page::class;

    /**
     * Finds a Page based upon the slug.
     *
     * @param string $slug
     * @return Page
     *@throws NoResultException|NonUniqueResultException
     */
    public function findBySlug($slug, $onlyPublished = true) {
        $qb = $this->createSelectQuery()
            ->andWhere('o.slug = :slug')
            ->setParameter('slug', $slug);
        $clauses = [new ExcludeTrashedScope()];
        if($onlyPublished) {
            $qb->andWhere('o.stage = :stage')
               ->setParameter('stage', Page::STAGE_PUBLISHED);
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
     * @return Templatable|null
     * @throws NonUniqueResultException
     */
    public function findByTemplateKey($key, $onlyPublished = true) {
        try {
            return $this->findBySlug($key, $onlyPublished);
        } catch(NoResultException $e) {
            return null;
        }
    }
}
