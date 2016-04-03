<?php

namespace OxygenModule\Pages\Controller;

use App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Oxygen\Core\Contracts\Routing\ResponseFactory;
use Oxygen\Core\Http\Notification;
use Oxygen\Crud\Controller\Previewable;
use Preferences;
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
    use Previewable { getContent as getPreviewContent; }

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
     * @param string                                 $slug the URI slug
     * @return \Illuminate\Http\Response
     */
    public function getView($slug = '/') {
        try {
            $page = $this->repository->findBySlug($slug);
            return $this->getContent($page);
        } catch(NoResultException $e) {
            App::abort(404, "Slug not found");
        }
    }

    /**
     * Displays the page content.
     * This is also used by the Previewable trait to get load inside an <iframe>.
     *
     * @param mixed                                  $item
     * @return \Illuminate\Http\Response
     */
    public function getContent($item = null) {
        if($item != null) {
            $item = $this->getItem($item);
        }

        $content = $this->getPreviewContent($item)->render();

        // if we are doing a quick preview of just the content
        if(Input::has('content') || $item == null) {
            return view(Preferences::get('appearance.pages::contentView'))->with('content', $content);
        }

        return view(Preferences::get('appearance.pages::theme'), [
            'page' => $item,
            'title' => $item->getTitle(),
            'content' => $content,
            'options' => $item->getOptions(),
            'description' => $item->getDescription(),
            'tags' => $item->getTags(),
            'meta' => $item->getMeta()
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
