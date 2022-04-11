<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;

class AddContentViewPreference extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->findByKey('appearance.pages');
        $prefs = $item->getPreferences();
        Arr::set($prefs, 'contentView', 'oxygen/crud::content.content');
        $item->setPreferences($prefs);
        $preferences->persist($item);
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->findByKey('appearance.pages');
        $prefs = $item->getPreferences();
        Arr::set($prefs, 'contentView', null);
        $item->setPreferences($prefs);
        $preferences->persist($item);
    }
}
