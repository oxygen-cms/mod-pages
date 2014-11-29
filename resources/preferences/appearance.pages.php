<?php

use Oxygen\Preferences\Loader\ConfigLoader;

Preferences::register('appearance.pages', function($schema) {
    $schema->setTitle('Pages');
    $schema->setLoader(new ConfigLoader(App::make('config'), 'oxygen/pages::config'));

    $schema->makeFields([
        '' => [
            '' => [
                [
                    'name' => 'theme',
                    'validationRules' => ['view_exists']
                ]
            ],
        ]
    ]);
});