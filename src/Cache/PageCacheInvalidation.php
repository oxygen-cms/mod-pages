<?php


namespace OxygenModule\Pages\Cache;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use OxygenModule\Pages\Entity\Page;

class PageCacheInvalidation implements CacheInvalidationInterface {

    protected $resolver;

    protected $finder;

    protected $events;

    protected $entityManager;

    /**
     * Constructs the PageChangedSubscriber.
     *
     * @param \Illuminate\View\Engines\EngineResolver $resolver
     * @param \Illuminate\View\ViewFinderInterface    $finder
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     * @param \Doctrine\ORM\EntityManagerInterface    $em
     */
    public function __construct(EngineResolver $resolver, ViewFinderInterface $finder, Dispatcher $events, EntityManagerInterface $em) {
        $this->resolver = $resolver;
        $this->finder = $finder;
        $this->events = $events;
        $this->entityManager = $em;
    }

    /**
     * @param \OxygenModule\Pages\Entity\Page $page
     * @param string                          $old
     * @param string                          $new
     */
    public function contentChanged(Page $page, $old, $new) {
        $factory = new ViewFactory($this->resolver, $this->finder, $this->events);
        $factory->clearDependencies();

        $factory->model($page, 'content')->render(); // render but discard contents
        $oldDeps = $factory->getAndClearDependencies();

        $this->compileNewViewContent($page, $new, $factory);
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
            $this->entityManager->persist($item);
        }
        foreach($newDeps as $item) {
            $item->addEntityToBeInvalidated($page);
            $this->entityManager->persist($item);
        }
    }

    /**
     * @param \OxygenModule\Pages\Entity\Page       $page
     * @param string                                $content
     * @param \OxygenModule\Pages\Cache\ViewFactory $factory
     */
    protected function compileNewViewContent(Page $page, $content, ViewFactory $factory) {
        $path = $factory->pathFromModel(get_class($page), $page->getId(), 'content');

        $factory->string($content, $path, time())->render();
    }

    /**
     * The page was deleted.
     *
     * @param \OxygenModule\Pages\Entity\Page $page
     */
    public function pageRemoved(Page $page) {
        $factory = new ViewFactory($this->resolver, $this->finder, $this->events);
        $factory->clearDependencies();
        $factory->model($page, 'content')->render(); // render but discard contents
        $dependencies = $factory->getAndClearDependencies();

        foreach($dependencies as $item) {
            $item->removeEntityToBeInvalidated($page);
            $this->entityManager->persist($item);
        }
    }

}