<?php

use Oxygen\Core\Action\Action;
use Oxygen\Core\Action\Group;
use Oxygen\Core\Action\Factory\ActionFactory;
use Oxygen\Core\Html\Toolbar\ActionToolbarItem;
use Oxygen\Crud\BlueprintTrait\VersionableCrudTrait;
use Oxygen\Pages\Model\Page;

Blueprint::make('Page', function($blueprint) {
    $blueprint->setController('Oxygen\Pages\Controller\PagesController');
    $blueprint->setIcon('file-o');

    $blueprint->setToolbarOrders([
        'section' => [
            'getCreate', 'getTrash'
        ],
        'item' => [
            'getView',
            'getUpdate',
            'More' => ['postPublish', 'getInfo', 'deleteDelete', 'postRestore', 'deleteForce'],
            'Version' => ['postNewVersion', 'postMakeHeadVersion']
        ],
        'versionList' => [
            'deleteVersions'
        ]
    ]);

    $blueprint->useTrait(new VersionableCrudTrait());

    $blueprint->makeAction(
        [
            'name'          => 'getView',
            'pattern'       => '{slug?}',
            'group'         => new Group('pages'),
            'registerAtEnd' => true,
            'routeParametersCallback' => function(Action $action, array $options) {
                return [
                    $options['model']->slug
                ];
            },
            'customRouteCallback' => function(Action $action, $route) {
                $route->where('slug', '([a-z0-9/\-]+)');
            }
        ],
        new ActionFactory()
    );
    $blueprint->makeToolbarItem([
        'action'        => 'getView',
        'label'         => 'View',
        'icon'          => 'file-o',
        'shouldRenderCallback' => function(ActionToolbarItem $item, array $arguments) {
            return
                $item->shouldRenderBasic($arguments) &&
                !$arguments['model']->trashed() &&
                $arguments['model']->published();
        }
    ]);

    $blueprint->makeAction([
        'name'      => 'postPublish',
        'pattern'   => '{id}/publish',
        'method'    => 'POST'
    ]);
    $blueprint->makeToolbarItem([
        'action'        => 'postPublish',
        'label'         => 'Publish',
        'icon'          => 'globe',
    ])->addDynamicCallback(function(ActionToolbarItem $item, array $arguments) {
        if($arguments['model']->published()) {
            $item->label = 'Unpublish';
            $item->icon = 'archive';
        }
    });

    $blueprint->makeFields([
        [
            'name'              => 'id',
            'label'             => 'ID'
        ],
        [
            'name'              => 'version_head'
        ],
        [
            'name'              => 'slug',
            'editable'          => true,
            'validationRules'   => [
                'required',
                'slug',
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
            'name'              => 'description',
            'type'              => 'textarea',
            'editable'          => true,
            'attributes'        => [ 'rows' => 3 ]
        ],
        [
            'name'              => 'tags',
            'type'              => 'textarea',
            'editable'          => true,
            'attributes'        => [ 'rows' => 2 ]
        ],
        [
            'name'              => 'meta',
            'type'              => 'editor-mini',
            'editable'          => true,
            'options'           => [
                'language'      => 'html',
                'mode'          => 'code'
            ]
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
            'name'              => 'options',
            'type'              => 'editor-mini',
            'editable'          => true,
            'validationRules'   => [ 'json' ],
            'options'           => [
                'language'      => 'json',
                'mode'          => 'code'
            ]
        ],
        [
            'name'      => 'stage',
            'type'      => 'select',
            'editable'  => true,
            'options'   => [
                Page::STAGE_DRAFT => 'Draft',
                Page::STAGE_PENDING_REVIEW => 'Pending Review',
                Page::STAGE_PUBLISHED => 'Published',
                Page::STAGE_ARCHIVED => 'Archived'
            ],
            'validationRules' => [
                'in:0,1,2,3'
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
