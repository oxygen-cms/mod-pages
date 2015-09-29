<?php


namespace OxygenModule\Pages\Cache;

use Oxygen\Core\View\Factory;

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
     * @param \OxygenModule\Pages\Cache\CacheInvalidator $entity the entity that this view depends on
     */
    public function viewDependsOnEntity(CacheInvalidator $entity) {
        $this->viewDependsOnEntities[] = $entity;
    }

    public function clearDependencies() {
        $this->viewDependsOnEntities = [];
    }

    /*
     * Returns the entities that the rendered views depended on.
     *
     * @return array
     */
    public function getAndClearDependencies() {
        $entities = $this->viewDependsOnEntities;
        $this->viewDependsOnEntities = [];
        return $entities;
    }

}