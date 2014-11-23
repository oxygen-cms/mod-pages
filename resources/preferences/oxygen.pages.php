<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('oxygen.pages', function($schema) {
    $schema->setTitle('Pages');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/pages::config'));

    $schema->makeFields([
        '' => [
            'Appearance' => [
                [
                    'name' => 'theme',
                    'validationRules' => ['view_exists']
                ]
            ],
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