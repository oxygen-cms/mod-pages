<?php

namespace Oxygen\Pages\Cache;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Oxygen\Pages\Entity\Page;
use Oxygen\Pages\Entity\Partial;

class EntityChangedSubscriber implements EventSubscriber {

    /**
     * Cache Interface
     *
     * @var CacheInterface
     */

    protected $cache;

    /**
     * Constructs the PageChangedSubscriber.
     *
     * @param CacheInterface $cache
     */

    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */

    public function getSubscribedEvents() {
        return [
            Events::preUpdate,
            Events::preRemove
        ];
    }

    /**
     * Invalidates the cache.
     *
     * @param LifecycleEventArgs $args
     * @return void
     */

    public function preUpdate(LifecycleEventArgs $args) {
        $this->invalidate($args);
    }

    /**
     * Invalidates the cache.
     *
     * @param LifecycleEventArgs $args
     * @return void
     */

    public function preRemove(LifecycleEventArgs $args) {
        $this->invalidate($args);
    }

    /**
     * Invalidates the cache.
     *
     * @param $args
     */

    protected function invalidate(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if($entity instanceof Page && $entity->isPublished()) {
            $this->cache->clear($entity->getSlug());
        }

        if($entity instanceof Partial && $entity->isPublished()) {
            $this->cache->clearAll();
        }
    }

}