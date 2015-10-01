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
use OxygenModule\Pages\Entity\Page;

class PageCacheSubscriber implements EventSubscriber {

    protected $viewFactory;

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
                    $item->removeEntityToBeInvalidated($entity);
                    $metadata = $args->getEntityManager()->getClassMetadata(get_class($item));
                    $uow->computeChangeSet($metadata, $item);
                }
                foreach($newDeps as $item) {
                    $item->addEntityToBeInvalidated($entity);
                    $metadata = $args->getEntityManager()->getClassMetadata(get_class($item));
                    $uow->computeChangeSet($metadata, $item);
                }
            }
        }

        foreach($uow->getScheduledEntityDeletions() as $entity) {
            $this->getView()->model($entity, 'content')->render(); // render but discard contents
            $dependencies = $this->getView()->getAndClearDependencies();

            foreach($dependencies as $item) {
                $item->removeEntityToBeInvalidated($entity);
                $metadata = $args->getEntityManager()->getClassMetadata(get_class($item));
                $uow->computeChangeSet($metadata, $item);
            }
        }
    }

    public function postPersist(LifecycleEventArgs $args) {
        if($args->getEntity() instanceof Page) {
            $this->getView()->model($args->getEntity(), 'content')->render(); // render but discard contents
            $dependencies = $this->getView()->getAndClearDependencies();

            foreach($dependencies as $item) {
                $item->addEntityToBeInvalidated($args->getEntity());
                $args->getEntityManager()->persist($item);
            }
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
            throw new ViewExecutionException('Page failed to compile', $e);
        }
    }

    public function getView() {
        if($this->viewFactory == null) {
            $this->viewFactory = new ViewFactory($this->resolver, $this->finder, $this->events);
        }
        return $this->viewFactory;
    }

}