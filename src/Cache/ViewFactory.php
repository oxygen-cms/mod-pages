<?php


namespace OxygenModule\Pages\Cache;

use Oxygen\Core\View\Factory;
use Oxygen\Data\Behaviour\CacheInvalidatorInterface;

class ViewFactory extends Factory {

    /**
     * The current view depends on these entities
     *
     * @var array
     */
    protected $viewDependsOnEntities;

    /**
     * Tells this custom view factory that the current view depends on the given entity.
     *
     * @param \Oxygen\Data\Behaviour\CacheInvalidatorInterface $entity the entity that this view depends on
     */
    public function viewDependsOnEntity(CacheInvalidatorInterface $entity) {
        $this->viewDependsOnEntities[] = $entity;
    }

    public function clearDependencies() {
        $this->viewDependsOnEntities = [];
    }

    /*
     * Returns the entities that the rendered views depended on.
     *
     * @return CacheInvalidatorInterface[]
     */
    public function getAndClearDependencies() {
        $entities = $this->viewDependsOnEntities;
        $this->viewDependsOnEntities = [];
        return $entities;
    }

}