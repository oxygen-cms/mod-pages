<?php

namespace OxygenModule\Pages\Controller;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Oxygen\Core\Http\Notification;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Preferences\Facades\Preferences;
use Oxygen\Crud\Controller\Publishable;
use OxygenModule\Pages\Cache\ViewExecutionException;
use OxygenModule\Pages\Fields\PageFieldSet;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Data\Exception\NoResultException;
use OxygenModule\Pages\Repository\PageRepositoryInterface;

class PagesController extends VersionableCrudController {

    use Publishable;
    use Previewable;

    /**
     * Constructs the PagesController.
     *
     * @param PageRepositoryInterface $repository
     * @param BlueprintManager $manager
     * @param PageFieldSet $fields
     * @throws \Oxygen\Core\Blueprint\BlueprintNotFoundException
     */
    public function __construct(PageRepositoryInterface $repository, BlueprintManager $manager, PageFieldSet $fields) {
        parent::__construct($repository, $manager->get('Page'), $fields);
    }

    /**
     * View a page with a theme.
     *
     * @param string                                 $slug the URI slug
     * @return View
     */
    public function getView($slug = '/') {
        try {
            $page = $this->repository->findBySlug($slug);
            return $this->getContent($page);
        } catch(NoResultException $e) {
            abort(404, "Slug not found");
        }
    }

    protected function decorateContent($content, $item) {
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

    protected function decoratePreviewContent($content) {
        return view(Preferences::get('appearance.pages::contentView'))->with('content', $content);
    }

    /**
     * Updates an entity.
     *
     * @param Request $request
     * @param mixed $item the item
     * @return \Illuminate\Http\Response
     */
    public function putUpdate(Request $request, $item) {
        try {
            return parent::putUpdate($request, $item);
        } catch(ViewExecutionException $e) {
            logger()->error($e);
            logger()->error($e->getPrevious());
            return notify(
                new Notification('PHP Error in Page Content', Notification::FAILED),
                ['input' => true]
            );
        }
    }

}
