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
     * The current view depends on these classes.
     *
     * @var array
     */
    protected $viewDependsOnAllEntities;

    /**
     * Tells this custom view factory that the current view depends on the given entity.
     *
     * @param \Oxygen\Data\Behaviour\CacheInvalidatorInterface $entity the entity that this view depends on
     */
    public function viewDependsOnEntity(CacheInvalidatorInterface $entity) {
        $this->viewDependsOnEntities[] = $entity;
    }

    /**
     * Tells this custom view factory that the current view depends on all entities of the given type.
     *
     * @param string $className the class of entity that this view depends on
     */
    public function viewDependsOnAllEntity($className) {
        $this->viewDependsOnAllEntities[] = $className;
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
        $entities = ['entities' => $this->viewDependsOnEntities, 'classes' => $this->viewDependsOnAllEntities];
        $this->viewDependsOnEntities = [];
        $this->viewDependsOnAllEntities = [];
        return $entities;
    }

}