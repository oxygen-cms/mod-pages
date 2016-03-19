<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Repository;

class AddContentViewPreference extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->findByKey('appearance.pages');
        $item->getPreferences()->set('contentView', 'oxygen/crud::content.content');
        $preferences->persist($item);
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->findByKey('appearance.pages');
        $item->getPreferences()->set('contentView', null);
        $preferences->persist($item);
    }
}
