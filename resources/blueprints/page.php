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
            'middleware' => 'oxygen.cache'
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

});
