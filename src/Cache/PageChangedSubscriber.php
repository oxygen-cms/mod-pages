<?php


namespace OxygenModule\Pages\Cache;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use Oxygen\Data\Behaviour\Timestamps;
use OxygenModule\Pages\Entity\Page;

class PageChangedSubscriber implements EventSubscriber {

    protected $viewEngineResolver;

    protected $viewFinder;

    protected $events;

    /**
     * @var \Illuminate\View\Engines\EngineResolver
     */
    private $resolver;

    /**
     * @var \Illuminate\View\ViewFinderInterface
     */
    private $finder;

    /**
     * Constructs the PageChangedSubscriber.
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
            Events::preUpdate,
            Events::preRemove
        ];
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
            if($args->hasChangedField('content')) {
                /*var_dump($args->getOldValue('content'));
                var_dump($args->getNewValue('content'));*/
                $factory = new ViewFactory($this->resolver, $this->finder, $this->events);
                $factory->clearDependencies();

                $factory->model($args->getEntity(), 'content')->render(); // render but discard contents
                $oldDeps = $factory->getAndClearDependencies();

                $this->compileNewViewContent($args, $args->getEntity(), $factory);
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
    }

    /**
     * @param \Doctrine\ORM\Event\PreUpdateEventArgs $args
     * @param                                        $model
     * @param                                        $factory
     */
    protected function compileNewViewContent(PreUpdateEventArgs $args, $model, ViewFactory $factory) {
        $path = $factory->pathFromModel(get_class($args->getEntity()), $args->getEntity()->getId(), 'content');

        $factory->string($args->getNewValue('content'), $path, $model->getUpdatedAt()->timestamp)->render();
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

}