<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Repository;

class AddPagesPreferences extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->make();
        $item->setKey('appearance.pages');
        $data = new Repository([]);
        $data->set('theme', 'oxygen/mod-pages::pages.view');
        $item->setPreferences($data);
        $preferences->persist($item, false);

        $item = $preferences->make();
        $item->setKey('modules.pages');
        $data = new Repository([]);
        $data->set('cache.enabled', true);
        $data->set('location', '/public/content/cache');
        $item->setPreferences($data);
        $preferences->persist($item, false);

        $preferences->flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $preferences->delete($preferences->findByKey('appearance.pages'), false);
        $preferences->delete($preferences->findByKey('modules.pages'), false);

        $preferences->flush();
    }
}
