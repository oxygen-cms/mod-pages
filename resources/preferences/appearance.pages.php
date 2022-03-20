<?php

use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;
use Oxygen\Preferences\Loader\DatabaseLoader;
use Oxygen\Preferences\Facades\Preferences;
use Oxygen\Preferences\Schema;
use Oxygen\Preferences\ThemeSpecificPreferencesFallback;
use Oxygen\Core\Theme\ThemeManager;

Preferences::register('appearance.pages', function(Schema $schema) {
    $schema->setTitle('Pages & Partials');
    $schema->setLoader(
        new DatabaseLoader(
            app(PreferenceRepositoryInterface::class),
            'appearance.pages',
            new ThemeSpecificPreferencesFallback(app(ThemeManager::class), 'appearance.pages')
        )
    );

    $schema->makeFields([
        [
            'name' => 'theme',
            'validationRules' => ['nullable', 'view_exists']
        ],
        [
            'name' => 'contentView',
            'label' => 'Standalone Content View',
            'validationRules' => ['nullable', 'view_exists']
        ]
    ]);
});


