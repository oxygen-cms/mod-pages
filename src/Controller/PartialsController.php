<?php

namespace Oxygen\Pages\Controller;

use Oxygen\Core\Blueprint\Manager as BlueprintManager;
use Oxygen\Crud\Controller\VersionableCrudController;

class PartialsController extends VersionableCrudController {

    /**
     * Constructs the AuthController.
     *
     * @param BlueprintManager $manager
     */

    public function __construct(BlueprintManager $manager) {
        parent::__construct($manager, 'Partial', 'Oxygen\Pages\Model\Partial');
    }

}
