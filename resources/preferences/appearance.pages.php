<?php

use Oxygen\Core\Facades\Preferences;
use Oxygen\Core\Preferences\Loader\PreferenceRepositoryInterface;
use Oxygen\Core\Preferences\Loader\DatabaseLoader;
use Oxygen\Core\Preferences\Schema;
use Oxygen\Core\Preferences\ThemeSpecificPreferencesFallback;
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
            'name' => 'contentStylesheet',
            'label' => 'CSS stylesheet which controls preview of content in the editor.',
            'validationRules' => []
        ],
        [
            'name' => 'contentView',
            'label' => 'Standalone Content View',
            'validationRules' => ['nullable', 'view_exists']
        ]
    ]);
});


