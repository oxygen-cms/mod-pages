<?php

use Oxygen\Core\Action\Factory\ActionFactory;
use Oxygen\Core\Action\Action;
use Oxygen\Core\Action\Group;
use Oxygen\Core\Html\Toolbar\ActionToolbarItem;
use Oxygen\Crud\BlueprintTrait\VersionableCrudTrait;

Blueprint::make('Partial', function($blueprint) {
    $blueprint->setController('Oxygen\Pages\Controller\PartialsController');
    $blueprint->setIcon('chain');

    $blueprint->setToolbarOrders([
        'section' => [
            'getCreate', 'getTrash'
        ],
        'item' => [
            'getUpdate',
            'More' => ['getInfo', 'deleteDelete', 'postRestore', 'deleteForce'],
            'Version' => ['postNewVersion', 'postMakeHeadVersion']
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
            'editable'          => true,
            'validationRules'   => [
                'required',
                'alpha_dot',
                'max:50',
                'unique_ignore_versions'
            ]
        ],
        [
            'name'              => 'title',
            'editable'          => true,
            'validationRules'   => [ 'required', 'max:50' ]
        ],
        [
            'name'              => 'author',
            'editable'          => true,
            'validationRules'   => [ 'max:50', 'name' ]
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
            'name'      => 'created_at'
        ],
        [
            'name'      => 'updated_at'
        ],
        [
            'name'      => 'deleted_at'
        ],
    ]);

    $blueprint->setTitleField('title');
});
