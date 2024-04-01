<?php

namespace OxygenModule\Pages\Controller;

use Illuminate\View\View;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Blueprint\BlueprintNotFoundException;
use Oxygen\Crud\Controller\BasicCrudApi;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Crud\Controller\SoftDeleteCrudApi;
use Oxygen\Crud\Controller\VersionableCrudApi;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use OxygenModule\Pages\Fields\PartialFieldSet;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;

class PartialsController extends VersionableCrudController {

    use Publishable;
    use Previewable;

    use BasicCrudApi, SoftDeleteCrudApi, VersionableCrudApi {
        VersionableCrudApi::getListQueryParameters insteadof BasicCrudApi, SoftDeleteCrudApi;
        SoftDeleteCrudApi::deleteDeleteApi insteadof BasicCrudApi;
    }

    const PER_PAGE = 50;

    const ALLOWED_SORT_FIELDS = ['title', 'key', 'updatedAt'];

    /**
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * Constructs the PagesController.
     *
     * @param PartialRepositoryInterface $repository
     * @param BlueprintManager $manager
     * @param PartialFieldSet $fields
     * @param PreferencesManager $preferencesManager
     * @throws BlueprintNotFoundException
     */
    public function __construct(PartialRepositoryInterface $repository, BlueprintManager $manager, PartialFieldSet $fields, PreferencesManager $preferencesManager) {
        parent::__construct($repository, $manager->get('Partial'), $fields);
        $this->preferences = $preferencesManager;
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
