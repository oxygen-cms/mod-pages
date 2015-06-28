<?php

use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;

Preferences::register('appearance.pages', function($schema) {
    $schema->setTitle('Pages');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'appearance.pages'));

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