<?php

namespace OxygenModule\Pages\Controller;

use Exception;
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
use Oxygen\Theme\ThemeManager;
use OxygenModule\Pages\Entity\Page;
use OxygenModule\Pages\Fields\PageFieldSet;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Data\Exception\NoResultException;
use OxygenModule\Pages\Repository\PageRepositoryInterface;

class PagesController extends VersionableCrudController {

    private const PAGE_VIEW_KEY = 'appearance.pages::theme';
    private const CONTENT_VIEW_KEY = 'appearance.pages::contentView';

    use Publishable;
    use Previewable;

    /**
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * Constructs the PagesController.
     *
     * @param PageRepositoryInterface $repository
     * @param BlueprintManager $manager
     * @param PageFieldSet $fields
     * @param PreferencesManager $preferencesManager
     * @param ThemeManager $themeManager
     * @throws BlueprintNotFoundException
     */
    public function __construct(PageRepositoryInterface $repository, BlueprintManager $manager, PageFieldSet $fields, PreferencesManager $preferencesManager, ThemeManager $themeManager) {
        parent::__construct($repository, $manager->get('Page'), $fields);
        $this->preferences = $preferencesManager;
        $this->themeManager = $themeManager;
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
    protected function decorateContent(string $content, Page $item) {
        $this->applyThemeOverrides($item);
        return view($this->preferences->get(self::PAGE_VIEW_KEY), [
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
//        $this->applyThemeOverrides()
//        $view =
//        if(isset($item->getOptions()['customTheme'])) {
//            $theme = $this->app(ThemeManager::class)->get($item->getOptions()['customTheme']);
//            if(isset($theme->getProvides()[self::CONTENT_VIEW_KEY])) {
//                $view = $theme->getProvides()[self::CONTENT_VIEW_KEY];
//            }
//        }

        return view($this->preferences->get(self::CONTENT_VIEW_KEY))->with('content', $content);
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
        } catch(Exception $e) {
            logger()->error($e);
            logger()->error($e->getPrevious());
            return notify(
                new Notification('PHP Error in Page Content', Notification::FAILED),
                ['input' => true]
            );
        }
    }

    /**
     * @param Page $page
     */
    private function applyThemeOverrides(Page $page) {
        if(isset($page->getOptions()['customTheme'])) {
            $this->themeManager->temporarilyOverrideTheme($page->getOptions()['customTheme']);
        }
    }

}
