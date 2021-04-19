<?php

namespace OxygenModule\Pages\Repository;

use \Doctrine\ORM\NoResultException as DoctrineNoResultException;
use Oxygen\Core\Templating\Templatable;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Data\Repository\Doctrine\Publishes;
use Oxygen\Data\Repository\Doctrine\Repository;
use Oxygen\Data\Repository\Doctrine\Versions;
use Oxygen\Data\Repository\ExcludeTrashedScope;
use Oxygen\Data\Repository\QueryParameters;
use OxygenModule\Pages\Entity\Page;

class DoctrinePageRepository extends Repository implements PageRepositoryInterface {

    use Versions, Publishes {
        Publishes::persist insteadof Versions;
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
     * @throws NoResultException
     * @return Page
     */
    public function findBySlug($slug) {
        $q = $this->getQuery(
            $this->createSelectQuery()
                 ->andWhere('o.stage = :stage')
                 ->andWhere('o.slug = :slug')
                 ->setParameter('stage', Page::STAGE_PUBLISHED)
                 ->setParameter('slug', $slug),
            new QueryParameters([new ExcludeTrashedScope()])
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
            return $this->findBySlug($key);
        } catch(NoResultException $e) {
            return null;
        }
    }
}
