<?php

use Oxygen\Core\Action\Action;
use Oxygen\Core\Action\Group;
use Oxygen\Core\Action\Factory\ActionFactory;
use Oxygen\Core\Html\Toolbar\ActionToolbarItem;
use Oxygen\Crud\BlueprintTrait\PublishableCrudTrait;
use Oxygen\Crud\BlueprintTrait\VersionableCrudTrait;
use Oxygen\Pages\Entity\Page;

Blueprint::make('Page', function($blueprint) {
    $blueprint->setController('Oxygen\Pages\Controller\PagesController');
    $blueprint->setIcon('file-o');

    $blueprint->setToolbarOrders([
        'section' => [
            'getCreate', 'getTrash'
        ],
        'item' => [
            'getView',
            'getPreview',
            'getUpdate,More' => ['postPublish', 'getInfo', 'deleteDelete', 'postRestore', 'deleteForce'],
            'Version' => ['postMakeDraft', 'postNewVersion', 'postMakeHeadVersion']
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
            'register'      => 'atEnd',
            'routeParametersCallback' => function(Action $action, array $options) {
                return [
                    $options['model']->getSlug()
                ];
            },
            'customRouteCallback' => function(Action $action, $route) {
                $route->where('slug', '([a-z0-9/\-]+)');
            },
            'afterFilters' => 'oxygen.cache'
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
                !$arguments['model']->isDeleted() &&
                $arguments['model']->isPublished();
        }
    ]);

    $blueprint->makeAction([
        'name'      => 'getPreview',
        'pattern'   => '{id}/preview'
    ]);
    $blueprint->makeToolbarItem([
        'action'        => 'getPreview',
        'label'         => 'Preview',
        'icon'          => 'eye'
    ]);

    $blueprint->makeAction([
        'name'      => 'getContent',
        'pattern'   => '{id}/content'
    ]);

    $blueprint->makeFields([
        [
            'name'              => 'id',
            'label'             => 'ID'
        ],
        [
            'name'              => 'slug',
            'editable'          => true,
            'description'       => Lang::get('oxygen/pages::descriptions.page.slug')
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
            'options'           => [
                'language'      => 'json',
                'mode'          => 'code'
            ]
        ],
        [
            'name'      => 'createdAt',
            'type'      => 'date'
        ],
        [
            'name'      => 'updatedAt',
            'type'      => 'date'
        ],
        [
            'name'      => 'deletedAt',
            'type'      => 'date'
        ],
        [
            'name'      => 'headVersion',
            'label'     => 'Head Version',
            'type'      => 'relationship',
            'editable'  => false,
            'options'   => [
                'type'       => 'manyToOne',
                'blueprint'  => 'Page',
                'allowNull' => true,
                'items' => function() {
                    $repo = App::make('Oxygen\Pages\Repository\PageRepositoryInterface');
                    return $repo->columns(['id', 'title']);
                }
            ]
        ]
    ]);

    $blueprint->useTrait(new PublishableCrudTrait());

    $stage = $blueprint->getField('stage');
    $stage->options = [
        Page::STAGE_DRAFT => 'Draft',
        Page::STAGE_PENDING_REVIEW => 'Pending Review',
        Page::STAGE_PUBLISHED => 'Published',
        Page::STAGE_ARCHIVED => 'Archived'
    ];

    $blueprint->setTitleField('title');
});
