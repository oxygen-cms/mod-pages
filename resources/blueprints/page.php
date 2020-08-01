<?php

use Oxygen\Core\Action\Action;
use Oxygen\Core\Action\Group;
use Oxygen\Core\Action\Factory\ActionFactory;
use Oxygen\Core\Html\Toolbar\ActionToolbarItem;
use Oxygen\Crud\BlueprintTrait\PreviewableCrudTrait;
use Oxygen\Crud\BlueprintTrait\PublishableCrudTrait;
use Oxygen\Crud\BlueprintTrait\VersionableCrudTrait;
use OxygenModule\Pages\Controller\PagesController;
use Oxygen\Core\Support\Facades\Blueprint;
use Oxygen\Crud\BlueprintTrait\SearchableCrudTrait;

Blueprint::make('Page', function(\Oxygen\Core\Blueprint\Blueprint $blueprint) {
    $blueprint->setController(PagesController::class);
    $blueprint->setIcon('file-o');

    $blueprint->setToolbarOrders([
        'section' => [
            'getList.search', 'getCreate', 'getTrash'
        ],
        'item' => [
            'getPreview',
            'getUpdate,More' => ['postPublish', 'getInfo', 'getView', 'deleteDelete', 'postRestore', 'deleteForce'],
            'Version' => ['postMakeDraft', 'postNewVersion', 'postMakeHeadVersion']
        ],
        'versionList' => [
            'deleteVersions'
        ]
    ]);

    $blueprint->makeAction(
        [
            'name'          => 'getView',
            'pattern'       => '{slug?}',
            'group'         => new Group('pages'),
            'register'      => Action::REGISTER_AT_END,
            'routeParametersCallback' => function(Action $action, array $options) {
                return [
                    $options['model']->getSlug()
                ];
            },
            'customRouteCallback' => function(Action $action, $route) {
                $route->where('slug', '([a-z0-9/\-]+)');
            },
            'middleware' => ['web'],
        ],
        new ActionFactory()
    );
    $blueprint->makeToolbarItem([
        'action'        => 'getView',
        'label'         => 'View on actual site',
        'icon'          => 'file-alt',
        'shouldRenderCallback' => function(ActionToolbarItem $item, array $arguments) {
            return
                $item->shouldRenderBasic($arguments) &&
                !$arguments['model']->isDeleted() &&
                $arguments['model']->isPublished();
        }
    ]);

    $blueprint->useTrait(new PreviewableCrudTrait());
    $blueprint->useTrait(new VersionableCrudTrait());
    $blueprint->useTrait(new PublishableCrudTrait());
    $blueprint->useTrait(new SearchableCrudTrait());

});
