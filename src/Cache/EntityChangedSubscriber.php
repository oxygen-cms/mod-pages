<?php

namespace OxygenModule\Pages\Cache;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
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
     * The config.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Constructs the PageChangedSubscriber.
     *
     * @param CacheInterface                          $cache
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(CacheInterface $cache, Dispatcher $events) {
        $this->cache = $cache;
        $this->events = $events;
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

        $this->events->fire('oxygen.pages.cache.invalidated', [$entity, $this->cache]);
    }

}