<?php

use Oxygen\Crud\BlueprintTrait\PublishableCrudTrait;
use Oxygen\Crud\BlueprintTrait\VersionableCrudTrait;
use Oxygen\Pages\Entity\Partial;

Blueprint::make('Partial', function($blueprint) {
    $blueprint->setController('Oxygen\Pages\Controller\PartialsController');
    $blueprint->setIcon('puzzle-piece');

    $blueprint->setToolbarOrders([
        'section' => [
            'getCreate', 'getTrash'
        ],
        'item' => [
            'getUpdate,More' => ['getInfo', 'postPublish', 'deleteDelete', 'postRestore', 'deleteForce'],
            'Version' => ['postMakeDraft', 'postNewVersion', 'postMakeHeadVersion']
        ],
        'versionList' => [
            'deleteVersions'
        ]
    ]);

    $blueprint->useTrait(new VersionableCrudTrait());

    $blueprint->makeFields([
        [
            'name'              => 'id',
            'label'             => 'ID'
        ],
        [
            'name'              => 'key',
            'editable'          => true
        ],
        [
            'name'              => 'title',
            'editable'          => true
        ],
        [
            'name'              => 'author',
            'editable'          => true
        ],
        [
            'name'              => 'content',
            'type'              => 'editor',
            'editable'          => true,
            'options'           => [
                'language'      => 'php'
            ]
        ],
        [
            'name'      => 'createdAt'
        ],
        [
            'name'      => 'updatedAt'
        ],
        [
            'name'      => 'deletedAt'
        ],
    ]);

    $blueprint->useTrait(new PublishableCrudTrait());

    $stage = $blueprint->getField('stage');
    $stage->options = [
        Partial::STAGE_DRAFT => 'Draft',
        Partial::STAGE_PUBLISHED => 'Published',
    ];

    $blueprint->setTitleField('title');
});
