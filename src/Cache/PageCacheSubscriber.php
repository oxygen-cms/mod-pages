<?php

namespace OxygenModule\Pages\Cache;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use OxygenModule\Pages\Entity\Page;

class PageCacheSubscriber implements EventSubscriber {

    /**
     * @var \Illuminate\View\Engines\EngineResolver
     */
    private $resolver;

    /**
     * @var \Illuminate\View\ViewFinderInterface
     */
    private $finder;

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    private $events;

    /**
     * Constructs the CacheInvalidationSubscriber.
     *
     * @param \Illuminate\View\Engines\EngineResolver $resolver
     * @param \Illuminate\View\ViewFinderInterface    $finder
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(EngineResolver $resolver, ViewFinderInterface $finder, Dispatcher $events) {
        $this->resolver = $resolver;
        $this->finder = $finder;
        $this->events = $events;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents() {
        return [
            Events::prePersist,
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
    public function prePersist(LifecycleEventArgs $args) {
        if($args->getEntity() instanceof Page) {
            $page = $args->getEntity();
            $factory = new ViewFactory($this->resolver, $this->finder, $this->events);
            $factory->clearDependencies();
            $factory->model($page, 'content')->render(); // render but discard contents
            $dependencies = $factory->getAndClearDependencies();

            foreach($dependencies as $item) {
                $item->addEntityToBeInvalidated($page);
                $args->getEntityManager()->persist($item);
            }
        }
    }

    /**
     * Invalidates the cache.
     *
     * @param PreUpdateEventArgs $args
     * @return void
     */
    public function preUpdate(PreUpdateEventArgs $args) {
        if($args->getEntity() instanceof Page) {
            $page = $args->getEntity();
            $factory = new ViewFactory($this->resolver, $this->finder, $this->events);
            $factory->clearDependencies();

            $this->compileViewContent($page, $args->getOldValue('updatedAt'), $args->getOldValue('content'), $factory); // render but discard contents
            $oldDeps = $factory->getAndClearDependencies();

            $this->compileViewContent($page, $args->getNewValue('updatedAt'), $args->getNewValue('content'), $factory);
            $newDeps = $factory->getAndClearDependencies();

            $removed = [];
            // bad algorithm i know
            foreach($oldDeps as $oldDep) {
                $found = false;
                foreach($newDeps as $newDep) {
                    if($oldDep === $newDep) {
                        $found = true;
                        break;
                    }
                }
                if(!$found) {
                    $removed[] = $oldDep;
                }
            }

            foreach($removed as $item) {
                $item->removeEntityToBeInvalidated($page);
                $args->getEntityManager()->persist($item);
            }
            foreach($newDeps as $item) {
                $item->addEntityToBeInvalidated($page);
                $args->getEntityManager()->persist($item);
            }
        }
    }

    /**
     * Invalidates the cache.
     *
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function preRemove(LifecycleEventArgs $args) {
        if($args->getEntity() instanceof Page) {
            $page = $args->getEntity();
            $factory = new ViewFactory($this->resolver, $this->finder, $this->events);
            $factory->clearDependencies();
            $factory->model($page, 'content')->render(); // render but discard contents
            $dependencies = $factory->getAndClearDependencies();

            foreach($dependencies as $item) {
                $item->removeEntityToBeInvalidated($page);
                $args->getEntityManager()->persist($item);
            }
        }
    }

    /**
     * @param \OxygenModule\Pages\Entity\Page       $page
     * @param string                                $content
     * @param DateTime                              $modified
     * @param \OxygenModule\Pages\Cache\ViewFactory $factory
     */
    protected function compileViewContent(Page $page, $content, DateTime $modified, ViewFactory $factory) {
        $path = $factory->pathFromModel(get_class($page), $page->getId(), 'content');

        $factory->string($content, $path, $modified->getTimestamp())->render();
    }

}