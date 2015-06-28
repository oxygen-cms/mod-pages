<?php

    use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
    use Oxygen\Preferences\Loader\DatabaseLoader;

    Preferences::register('modules.pages', function($schema) {
    $schema->setTitle('Pages');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'modules.pages'));

    $schema->makeFields([
        '' => [
            'Caching' => [
                [
                    'name' => 'cache.enabled',
                    'type' => 'toggle',
                    'label' => 'Cache Enabled'
                ],
                [
                    'name' => 'cache.location',
                    'label' => 'Cache Location'
                ],
            ]
        ]
    ]);
});