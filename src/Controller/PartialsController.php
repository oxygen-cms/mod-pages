<?php

namespace OxygenModule\Pages\Controller;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Crud\Controller\Previewable;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Crud\Controller\VersionableCrudController;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;
use OxygenModule\Pages\Fields\PartialFieldSet;
use Preferences;

class PartialsController extends VersionableCrudController {

    use Publishable;
    use Previewable { getContent as getPreviewContent; }

    /**
     * Constructs the PagesController.
     *
     * @param PartialRepositoryInterface $repository
     * @param BlueprintManager        $manager
     */
    public function __construct(PartialRepositoryInterface $repository, BlueprintManager $manager, PartialFieldSet $fields) {
        parent::__construct($repository, $manager->get('Partial'), $fields);
    }

    /**
     * Renders the content for this resource as HTML.
     *
     * @param $item
     * @return Response
     */
    public function getContent($item = null) {
        $content = $this->getPreviewContent($item)->render();

        return view(Preferences::get('appearance.pages::contentView'))->with('content', $content);
    }

}
