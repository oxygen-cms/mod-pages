<?php

namespace OxygenModule\Pages\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Oxygen\Data\Repository\QueryClauseInterface;
use OxygenModule\Pages\Entity\Page;

class FilterByParentPageClause implements QueryClauseInterface {

    private string $slug;

    public function __construct($slug) {
        $this->slug = $slug;
    }

    public function apply(QueryBuilder $qb, string $alias): QueryBuilder {
        $slugParts = explode('/', $this->slug);
        $i = 1;
        while($nextPart = array_pop($slugParts)) {
            $iMinusOne = $i-1;
            $prev = $i === 1 ? $alias : "d$iMinusOne";
            $qb = $qb->innerJoin(Page::class, "d$i", Join::WITH, "$prev.parent = d$i.id")
                ->andWhere("d$i.slugPart = :name$i")
                ->andWhere($qb->expr()->orX("d$i.deletedAt is NULL", "d$i.deletedAt > :currentTimestamp"))
                ->setParameter("name$i", $nextPart);
            $i++;
        }
        $iMinusOne = $i-1;
        $prev = $i === 1 ? $alias : "d$iMinusOne";
        $qb->andWhere("$prev.parent is NULL");
        logger()->info($qb->getQuery()->getSQL() . "\n" . print_r($qb->getParameters(), true));
        return $qb;
    }
}