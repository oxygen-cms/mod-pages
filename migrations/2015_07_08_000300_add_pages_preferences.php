<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface;
use Oxygen\Preferences\Repository;

class AddPagesPreferences extends Migration {

    /**
     * Run the migrations.
     *
     * @param \Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface $preferences
     */
    public function up(PreferenceRepositoryInterface $preferences) {
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
     *
     * @param \Oxygen\Preferences\Loader\Database\PreferenceRepositoryInterface $preferences
     */
    public function down(PreferenceRepositoryInterface $preferences) {
        $preferences->delete($preferences->findByKey('appearance.pages'));
        $preferences->delete($preferences->findByKey('modules.pages'));
    }
}
