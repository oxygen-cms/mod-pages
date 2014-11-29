<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('modules.pages', function($schema) {
    $schema->setTitle('Pages');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/pages::config'));

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