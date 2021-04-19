<?php

use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;
use Oxygen\Preferences\Facades\Preferences;
use Oxygen\Preferences\Schema;

Preferences::register('appearance.pages', function(Schema $schema) {
    $schema->setTitle('Pages & Partials');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'appearance.pages'));

    $schema->makeFields([
        '' => [
            'Pages' => [
                [
                    'name' => 'theme',
                    'validationRules' => ['nullable', 'view_exists']
                ],
                [
                    'name' => 'contentView',
                    'label' => 'Standalone Content View',
                    'validationRules' => ['nullable', 'view_exists']
                ]
            ]
        ]
    ]);
});


