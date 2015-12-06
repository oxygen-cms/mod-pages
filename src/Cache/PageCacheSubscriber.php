<?php

namespace OxygenModule\Pages\Cache;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use Oxygen\Data\Cache\CacheSettingsRepositoryInterface;
use OxygenModule\Pages\Entity\Page;

class PageCacheSubscriber implements EventSubscriber {

    protected $viewFactory;

    protected $cacheSettings;

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
    public function __construct(EngineResolver $resolver, ViewFinderInterface $finder, Dispatcher $events, CacheSettingsRepositoryInterface $cacheSettings) {
        $this->resolver = $resolver;
        $this->finder = $finder;
        $this->events = $events;
        $this->cacheSettings = $cacheSettings;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents() {
        return [
            Events::postPersist,
            Events::onFlush
        ];
    }

    public function onFlush(OnFlushEventArgs $args) {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach($uow->getScheduledEntityUpdates() as $entity) {
            if($entity instanceof Page) {
                $changeSet = $uow->getEntityChangeSet($entity);
                if(!isset($changeSet['content'])) { continue; }
                $this->compileViewContent($entity, $changeSet['content'][0], null, $this->getView()); // render but discard contents
                $oldDeps = $this->getView()->getAndClearDependencies();
                $this->compileViewContent($entity, $changeSet['content'][1], null, $this->getView());
                $newDeps = $this->getView()->getAndClearDependencies();

                $entitiesRemoved = $this->arrayRemoved($oldDeps['entities'], $newDeps['entities']);
                $classesRemoved = $this->arrayRemoved($oldDeps['classes'], $newDeps['classes']);

                foreach($entitiesRemoved as $item) {
                    $item->removeEntityToBeInvalidated($entity);
                    $metadata = $args->getEntityManager()->getClassMetadata(get_class($item));
                    $uow->computeChangeSet($metadata, $item);
                }
                foreach($newDeps['entities'] as $item) {
                    $item->addEntityToBeInvalidated($entity);
                    $metadata = $args->getEntityManager()->getClassMetadata(get_class($item));
                    $uow->computeChangeSet($metadata, $item);
                }
                foreach($classesRemoved as $class) {
                    $this->cacheSettings->remove($class, $entity);
                }
                foreach($newDeps['classes'] as $class) {
                    $this->cacheSettings->add($class, $entity);
                }
                $this->cacheSettings->persist(true);
            }
        }

        foreach($uow->getScheduledEntityDeletions() as $entity) {
            if($entity instanceof Page) {
                $this->getView()->model($entity, 'content')->render(); // render but discard contents
                $dependencies = $this->getView()->getAndClearDependencies();

                foreach($dependencies['entities'] as $item) {
                    $item->removeEntityToBeInvalidated($entity);
                    $metadata = $args->getEntityManager()->getClassMetadata(get_class($item));
                    $uow->computeChangeSet($metadata, $item);
                }
                foreach($dependencies['classes'] as $class) {
                    $this->cacheSettings->remove($class, $entity);
                }
                $this->cacheSettings->persist(true);
            }
        }
    }

    private function arrayRemoved($old, $new) {
        $removed = [];
        // bad algorithm i know
        foreach($old as $oldItem) {
            $found = false;
            foreach($new as $newItem) {
                if($oldItem === $newItem) {
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                $removed[] = $oldItem;
            }
        }
        return $removed;
    }

    public function postPersist(LifecycleEventArgs $args) {
        if($args->getEntity() instanceof Page) {
            $this->getView()->model($args->getEntity(), 'content')->render(); // render but discard contents
            $dependencies = $this->getView()->getAndClearDependencies();

            foreach($dependencies['entities'] as $item) {
                $item->addEntityToBeInvalidated($args->getEntity());
                $args->getEntityManager()->persist($item);
            }
            foreach($dependencies['classes'] as $class) {
                $this->cacheSettings->add($class, $args->getEntity());
            }
            $this->cacheSettings->persist();
        }
        $args->getEntityManager()->flush();
    }

    /**
     * @param \OxygenModule\Pages\Entity\Page       $page
     * @param string                                $content
     * @param DateTime                              $modified
     * @param \OxygenModule\Pages\Cache\ViewFactory $factory
     * @throws \OxygenModule\Pages\Cache\ViewExecutionException
     */
    protected function compileViewContent(Page $page, $content, $modified, ViewFactory $factory) {
        $path = $factory->pathFromModel(get_class($page), $page->getId(), 'content');

        try {
            $factory->string($content, $path, $modified == null ? 0 : $modified->getTimestamp())->render();
        } catch(Exception $e) {
            throw new ViewExecutionException('Page failed to compile', 0, $e);
        }
    }

    public function getView() {
        if($this->viewFactory == null) {
            $this->viewFactory = new ViewFactory($this->resolver, $this->finder, $this->events);
        }
        return $this->viewFactory;
    }

}