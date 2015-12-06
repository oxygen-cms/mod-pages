<?php

namespace OxygenModule\Pages\Controller;

use App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use Oxygen\Preferences\PreferencesManager;
use Oxygen\Crud\Controller\Publishable;
use OxygenModule\Pages\Cache\CacheInvalidationInterface;
use OxygenModule\Pages\Cache\ViewExecutionException;
use OxygenModule\Pages\Fields\PageFieldSet;
use View;
use Input;
use Log;
use Lang;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Data\Exception\NoResultException;
use OxygenModule\Pages\Repository\PageRepositoryInterface;

class PagesController extends VersionableCrudController {

    use Publishable;

    /**
     * Constructs the PagesController.
     *
     * @param PageRepositoryInterface $repository
     * @param BlueprintManager        $manager
     */
    public function __construct(PageRepositoryInterface $repository, BlueprintManager $manager, PageFieldSet $fields) {
        parent::__construct($repository, $manager->get('Page'), $fields);
    }

    /**
     * View a page with a theme.
     *
     * @param string $slug the URI slug
     * @return Response
     */
    public function getView($slug = '/', PreferencesManager $preferences) {
        try {
            $page = $this->repository->findBySlug($slug);
            return $this->getContent($page, $preferences);
        } catch(NoResultException $e) {
            App::abort(404, "Slug not found");
        }
    }

    /**
     * Preview the page.
     *
     * @param mixed $item
     * @return Response
     */
    public function getPreview($item) {
        $item = $this->getItem($item);

        return View::make('oxygen/mod-pages::pages.preview', [
            'item' => $item,
            'fields' => $this->crudFields,
            'title' => Lang::get('oxygen/mod-pages::ui.preview')
        ]);
    }

    /**
     * Displays the page content.
     *
     * @param mixed $item
     * @return Response
     */
    public function getContent($item, PreferencesManager $preferences) {
        $page = $this->getItem($item);

        $content = View::model($page, 'content')->with(['page' => $page])->render();
        $options = $page->getOptions();

        return View::make($preferences->get('appearance.pages::theme'), [
            'page' => $page,
            'title' => $page->getTitle(),
            'content' => $content,
            'options' => $options,
            'description' => $page->getDescription(),
            'tags' => $page->getTags(),
            'meta' => $page->getMeta()
        ]);
    }

    /**
     * Updates an entity.
     *
     * @param mixed                                          $item the item
     * @param \Oxygen\Core\Contracts\Routing\ResponseFactory $response
     * @return \Illuminate\Http\Response
     */
    public function putUpdate($item, ResponseFactory $response) {
        try {
            return parent::putUpdate($item, $response);
        } catch(ViewExecutionException $e) {
            Log::error($e);
            Log::error($e->getPrevious());
            return $response->notification(
                new Notification('PHP Error in Page Content', Notification::FAILED),
                ['input' => true]
            );
        }
    }

}
