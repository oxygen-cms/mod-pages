<?php

namespace OxygenModule\Pages\Controller;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Crud\Controller\VersionableCrudController;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;
use OxygenModule\Pages\Fields\PartialFieldSet;
use Oxygen\Preferences\Facades\Preferences;

class PartialsController extends VersionableCrudController {

    use Publishable;
    use Previewable;

    /**
     * Constructs the PagesController.
     *
     * @param PartialRepositoryInterface $repository
     * @param BlueprintManager $manager
     * @throws \Oxygen\Core\Blueprint\BlueprintNotFoundException
     */
    public function __construct(PartialRepositoryInterface $repository, BlueprintManager $manager, PartialFieldSet $fields) {
        parent::__construct($repository, $manager->get('Partial'), $fields);
    }

    /**
     * @param $content
     * @return \Illuminate\View\View
     */
    protected function decoratePreviewContent($content) {
        return view(Preferences::get('appearance.pages::contentView'))->with('content', $content);
    }

}
