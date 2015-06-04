<?php

namespace Oxygen\Pages\Cache;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Illuminate\Config\Repository;
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
     * @var \Illuminate\Config\Repository
     */

    protected $config;

    /**
     * Constructs the PageChangedSubscriber.
     *
     * @param CacheInterface $cache
     * @param Repository     $config
     */
    public function __construct(CacheInterface $cache, Repository $config) {
        $this->cache = $cache;
        $this->config = $config;
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

        foreach($this->config->get('oxygen/pages::cache.entities') as $class => $callable) {
            if(get_class($entity) == $class) {
                $callable($entity, $this->cache);
            }
        }
    }

}