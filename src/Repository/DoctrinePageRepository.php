<?php

namespace OxygenModule\Pages\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Oxygen\Core\Templating\Templatable;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishes;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Data\Repository\ExcludeTrashedScope;
use Oxygen\Data\Repository\ExcludeVersionsScope;
use Oxygen\Data\Repository\QueryParameters;
use OxygenModule\Media\Entity\MediaDirectory;
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
     * @param bool $onlyPublished
     * @return Page
     * @throws NonUniqueResultException|NoResultException
     */
    public function findBySlug($slug, bool $onlyPublished = true): Page {
        $slugParts = $slug === '/' ?  ['/'] : explode('/', $slug);
        $finalPart = array_pop($slugParts);

        $qb = $this->entities
            ->createQueryBuilder()
            ->select('o')
            ->from($this->entityName, 'o')
            ->andWhere('o.slugPart = :name0')
            ->setParameter('name0', $finalPart);

        $clauses = [new ExcludeTrashedScope(), new FilterByParentPageClause(implode('/', $slugParts))];

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
     * @param bool $onlyPublished
     * @return Page|null
     * @throws NonUniqueResultException
     */
    public function findByTemplateKey($key, bool $onlyPublished = true): ?Page {
        try {
            return $this->findBySlug($key, $onlyPublished);
        } catch(NoResultException $e) {
            return null;
        }
    }
}
