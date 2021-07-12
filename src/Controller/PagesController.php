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
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
     * @param TwigTemplateCompiler $templating
     * @param string $slug the URI slug
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getView(TwigTemplateCompiler $templating, $slug = '/') {
        try {
            $page = $this->repository->findBySlug($slug);
            $view = $this->getContent($templating, null, true, $page);
            $response = response($view);
            if(auth()->guest()) {
                // TODO: make this configurable by a preference item...
                $response->header('Cache-Control', 'public, max-age=3600');
            }
            return $response;
        } catch(NoResultException $e) {
            abort(404, "Slug not found");
            return null;
        }
    }

    /**
     * @param string $content
     * @param null|Page $page
     * @return View
     * @throws PreferenceNotFoundException
     */
    protected function decorateContent(string $content, ?Page $page) {
        $this->applyThemeOverrides($page);
        return view($this->preferences->get(self::PAGE_VIEW_KEY), [
            'page' => $page,
            'title' => $page !== null ? $page->getTitle() : null,
            'content' => $content,
            'options' => $page !== null ? $page->getOptions() : [],
            'description' => $page !== null ? $page->getDescription() : null,
            'tags' => $page !== null ? $page->getTags() : null,
            'meta' => $page !== null ? $page->getMeta() : null
        ]);
    }

    /**
     * @param string $content
     * @param Page|null $page
     * @return View
     * @throws PreferenceNotFoundException
     */
    protected function decoratePreviewContent(string $content, ?Page $page) {
        $this->applyThemeOverrides($page);
        return view($this->preferences->get(self::CONTENT_VIEW_KEY))->with('content', $content);
    }

    /**
     * @param Page|null $page
     */
    private function applyThemeOverrides(?Page $page) {
        if($page === null) {
            return;
        }
        if(isset($page->getOptions()['customTheme'])) {
            $this->themeManager->temporarilyOverrideTheme($page->getOptions()['customTheme']);
        }
    }

}
