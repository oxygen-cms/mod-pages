<?php

namespace OxygenModule\Pages\Controller;

use Illuminate\Routing\Controller;
use Oxygen\Core\Controller\BasicCrudTrait;
use Oxygen\Core\Controller\PreviewableCrudTrait;
use Oxygen\Core\Controller\PublishableCrudTrait;
use Oxygen\Core\Controller\SoftDeleteCrudTrait;
use Oxygen\Core\Controller\VersionableCrudTrait;
use Oxygen\Core\Preferences\PreferenceNotFoundException;
use Oxygen\Core\Preferences\PreferencesManager;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;

class PartialsController extends Controller {

    use PublishableCrudTrait;
    use PreviewableCrudTrait;

    use BasicCrudTrait, SoftDeleteCrudTrait, VersionableCrudTrait {
        VersionableCrudTrait::getListQueryParameters insteadof BasicCrudTrait, SoftDeleteCrudTrait;
        SoftDeleteCrudTrait::deleteDeleteApi insteadof BasicCrudTrait;
    }

    const PER_PAGE = 50;

    const ALLOWED_SORT_FIELDS = ['title', 'key', 'updatedAt'];

    const LANG_MAPPINGS = [
        'resource' => 'Partial',
        'pluralResource' => 'Partials'
    ];

    protected $repository;

    /**
     * @var PreferencesManager
     */
    private $preferences;

    public function __construct(PartialRepositoryInterface $repository, PreferencesManager $preferencesManager) {
        $this->repository = $repository;
        $this->preferences = $preferencesManager;
        BasicCrudTrait::setupLangMappings(self::LANG_MAPPINGS);
    }

    protected function getItem($item) {
        return is_object($item) ? $item : $this->repository->find((int) $item);
    }

    /**
     * @param string $content
     * @return \Illuminate\Contracts\View\View
     * @throws PreferenceNotFoundException
     */
    protected function decoratePreviewContent(string $content): \Illuminate\Contracts\View\View {
        return view($this->preferences->get('appearance.pages::contentView'))->with('content', $content);
    }

}
