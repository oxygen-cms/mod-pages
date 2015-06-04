<?php

namespace Oxygen\Pages\Controller;

use Oxygen\Core\Blueprint\Manager as BlueprintManager;
use Oxygen\Crud\Controller\Publishable;
use Oxygen\Crud\Controller\VersionableCrudController;
use Oxygen\Pages\Repository\PartialRepositoryInterface;

class PartialsController extends VersionableCrudController {

    use Publishable;

    /**
     * Constructs the PagesController.
     *
     * @param PartialRepositoryInterface $repository
     * @param BlueprintManager        $manager
     */
    public function __construct(PartialRepositoryInterface $repository, BlueprintManager $manager) {
        parent::__construct($repository, $manager, 'Partial');
    }

}
