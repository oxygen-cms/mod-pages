<?php

namespace OxygenModule\Pages\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Oxygen\Core\Blueprint\BlueprintNotFoundException;
use Oxygen\Core\Http\Notification;
use Oxygen\Core\Templating\TwigTemplateCompiler;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
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
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * Constructs the PagesController.
     *
     * @param PageRepositoryInterface $repository
     * @param BlueprintManager $manager
     * @param PageFieldSet $fields
     * @throws BlueprintNotFoundException
     */
    public function __construct(PageRepositoryInterface $repository, BlueprintManager $manager, PageFieldSet $fields, PreferencesManager $preferencesManager) {
        parent::__construct($repository, $manager->get('Page'), $fields);
        $this->preferences = $preferencesManager;
    }

    /**
     * View a page with a theme.
     *
     * @param string $slug the URI slug
     * @param TwigTemplateCompiler $templating
     * @return View
     */
    public function getView(TwigTemplateCompiler $templating, $slug = '/') {
        try {
            $page = $this->repository->findBySlug($slug);
            return $this->getContent($page, $templating);
        } catch(NoResultException $e) {
            abort(404, "Slug not found");
            return null;
        }
    }

    /**
     * @param string $content
     * @param mixed $item
     * @return View
     * @throws PreferenceNotFoundException
     */
    protected function decorateContent($content, $item) {
        return view($this->preferences->get('appearance.pages::theme'), [
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
     * @param string $content
     * @return View
     * @throws PreferenceNotFoundException
     */
    protected function decoratePreviewContent($content) {
        return view($this->preferences->get('appearance.pages::contentView'))->with('content', $content);
    }

    /**
     * Updates an entity.
     *
     * @param Request $request
     * @param mixed $item the item
     * @return Response
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
