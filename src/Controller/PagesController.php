<?php

namespace OxygenModule\Pages\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Oxygen\Core\Blueprint\BlueprintNotFoundException;
use Oxygen\Core\Templating\TwigTemplateCompiler;
use Oxygen\Crud\Controller\BasicCrudApi;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Crud\Controller\SoftDeleteCrudApi;
use Oxygen\Crud\Controller\VersionableCrudApi;
use Oxygen\Data\Repository\QueryParameters;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use Oxygen\Core\Theme\ThemeManager;
use OxygenModule\Pages\Entity\Page;
use OxygenModule\Pages\Fields\PageFieldSet;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Data\Exception\NoResultException;
use OxygenModule\Pages\Repository\FilterByParentPageClause;
use OxygenModule\Pages\Repository\PageRepositoryInterface;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Webmozart\Assert\Assert;

class PagesController extends VersionableCrudController {

    private const PAGE_VIEW_KEY = 'appearance.pages::theme';
    private const CONTENT_VIEW_KEY = 'appearance.pages::contentView';

    const ALLOWED_SORT_FIELDS = ['title', 'slugPart', 'description', 'updatedAt'];

    use Publishable;
    use Previewable;

    use BasicCrudApi, SoftDeleteCrudApi, VersionableCrudApi {
        VersionableCrudApi::getListQueryParameters as versionableCrudQueryParameters;
        SoftDeleteCrudApi::deleteDeleteApi insteadof BasicCrudApi;
    }

    const PER_PAGE = 25;

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
     * @param Request $request
     * @return QueryParameters
     * @throws ReflectionException
     */
    protected function getListQueryParameters(Request $request): QueryParameters {
        $queryParameters = $this->versionableCrudQueryParameters($request);
        $path = $request->get('path', null);
        if($path !== null) {
            $queryParameters = $queryParameters->addClause(new FilterByParentPageClause($path));
        }
        return $queryParameters;
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
            Assert::isInstanceOf($this->repository, PageRepositoryInterface::class);
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
        }
    }

    /**
     * @param string $content
     * @param null|Page $page
     * @return View
     * @throws PreferenceNotFoundException
     */
    protected function decorateContent(string $content, ?Page $page) {
        return $this->applyThemeOverrides($page, function() use($page, $content) {
            return view($this->preferences->get(self::PAGE_VIEW_KEY, 'oxygen/mod-pages::pages.view'), [
                'page' => $page,
                'title' => $page !== null ? $page->getTitle() : null,
                'content' => $content,
                'options' => $page !== null ? $page->getOptions() : [],
                'description' => $page !== null ? $page->getDescription() : null,
                'tags' => $page !== null ? $page->getTags() : null,
                'meta' => $page !== null ? $page->getMeta() : null
            ]);
        });
    }

    /**
     * @param string $content
     * @param Page|null $page
     * @return View
     * @throws PreferenceNotFoundException
     */
    protected function decoratePreviewContent(string $content, ?Page $page) {
        return $this->applyThemeOverrides($page, function() use($content) {
            return view($this->preferences->get(self::CONTENT_VIEW_KEY, 'oxygen/mod-pages::pages.content'))->with('content', $content);
        });
    }

    /**
     * @param Page|null $page
     * @param callable $inner
     * @return mixed
     */
    private function applyThemeOverrides(?Page $page, callable $inner) {
        if($page === null || !isset($page->getOptions()['customTheme'])) {
            return $inner();
        } else {
            $this->themeManager->temporarilyOverrideTheme($page->getOptions()['customTheme']);
            $ret = $inner();
            $this->themeManager->temporarilyOverrideTheme(null);
            return $ret;
        }
    }

    /**
     * @throws PreferenceNotFoundException
     */
    public function getThemeDetails(PreferencesManager $prefs): JsonResponse {
        return response()->json([
            'contentStylesheet' => $prefs->get('appearance.pages::contentStylesheet')
        ]);
    }

}
