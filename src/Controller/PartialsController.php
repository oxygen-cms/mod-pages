<?php

namespace OxygenModule\Pages\Controller;

use Illuminate\View\View;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Blueprint\BlueprintNotFoundException;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Preferences\PreferencesManager;
use OxygenModule\Pages\Fields\PartialFieldSet;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;

class PartialsController extends VersionableCrudController {

    use Publishable;
    use Previewable;

    /**
     * @var PreferencesManager
     */
    private $preferences;

    /**
     * Constructs the PagesController.
     *
     * @param PartialRepositoryInterface $repository
     * @param BlueprintManager $manager
     * @throws BlueprintNotFoundException
     */
    public function __construct(PartialRepositoryInterface $repository, BlueprintManager $manager, PartialFieldSet $fields, PreferencesManager $preferencesManager) {
        parent::__construct($repository, $manager->get('Partial'), $fields);
        $this->preferences = $preferencesManager;
    }

    /**
     * @param string $content
     * @return View
     */
    protected function decoratePreviewContent($content) {
        return view($this->preferences->get('appearance.pages::contentView'))->with('content', $content);
    }

}
