<?php

use Oxygen\Crud\BlueprintTrait\PreviewableCrudTrait;
use Oxygen\Crud\BlueprintTrait\PublishableCrudTrait;
use Oxygen\Crud\BlueprintTrait\VersionableCrudTrait;
use OxygenModule\Pages\Controller\PartialsController;
use Oxygen\Core\Support\Facades\Blueprint;

Blueprint::make('Partial', function(\Oxygen\Core\Blueprint\Blueprint $blueprint) {
    $blueprint->setController(PartialsController::class);
    $blueprint->setIcon('puzzle-piece');

    $blueprint->setToolbarOrders([
        'section' => [
            'getCreate'
        ],
        'item' => [
            'postPublish',
            'getPreview',
            'getUpdate,More' => ['postNewVersion', 'postMakeHeadVersion', 'deleteDelete', 'postRestore', 'deleteForce'],
        ],
        'versionList' => [
            'deleteVersions'
        ]
    ]);

    $blueprint->useTrait(new PreviewableCrudTrait());
    $blueprint->useTrait(new VersionableCrudTrait());
    $blueprint->useTrait(new PublishableCrudTrait());
});
