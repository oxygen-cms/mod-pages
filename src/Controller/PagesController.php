<?php

namespace Oxygen\Pages\Controller;

use App;
use Config;
use Oxygen\Crud\Controller\Publishable;
use View;
use Response;
use Lang;

use Oxygen\Core\Blueprint\Manager as BlueprintManager;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Data\Exception\NoResultException;
use Oxygen\Pages\Repository\PageRepositoryInterface;

class PagesController extends VersionableCrudController {

    use Publishable;

    /**
     * Constructs the PagesController.
     *
     * @param PageRepositoryInterface $repository
     * @param BlueprintManager        $manager
     */
    public function __construct(PageRepositoryInterface $repository, BlueprintManager $manager) {
        parent::__construct($repository, $manager, 'Page');
    }

    /**
     * View a page with a theme.
     *
     * @param string $slug the URI slug
     * @return Response
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
     * Preview the page.
     *
     * @param mixed $item
     * @return Response
     */
    public function getPreview($item) {
        $item = $this->getItem($item);

        return View::make('oxygen/pages::pages.preview', [
            'item' => $item,
            'title' => Lang::get('oxygen/pages::ui.preview')
        ]);
    }

    /**
     * Displays the page content.
     *
     * @param mixed $item
     * @return Response
     */
    public function getContent($item) {
        $page = $this->getItem($item);

        $content = View::model($page, 'content')->with(['page' => $page])->render();
        $options = $page->getOptions();

        return View::make(Config::get('oxygen/pages::theme'), [
            'page' => $page,
            'title' => $page->getTitle(),
            'content' => $content,
            'options' => $options,
            'description' => $page->getDescription(),
            'tags' => $page->getTags(),
            'meta' => $page->getMeta()
        ]);
    }

}
