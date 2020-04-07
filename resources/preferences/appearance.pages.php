<?php

use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;
use Oxygen\Preferences\Facades\Preferences;

Preferences::register('appearance.pages', function(\Oxygen\Preferences\Schema $schema) {
    $schema->setTitle('Pages & Partials');
    $schema->setLoader(new DatabaseLoader(app(PreferenceRepositoryInterface::class), 'appearance.pages'));

    $schema->makeFields([
        '' => [
            'Pages' => [
                [
                    'name' => 'theme',
                    'validationRules' => ['view_exists']
                ],
                [
                    'name' => 'contentView',
                    'label' => 'Standalone Content View',
                    'validationRules' => ['view_exists']
                ]
            ]
        ]
    ]);
});