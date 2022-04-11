<?php

use Illuminate\Database\Migrations\Migration;
use Oxygen\Preferences\Loader\PreferenceRepositoryInterface;

class AddPagesPreferences extends Migration {

    /**
     * Run the migrations.
     */
    public function up() {
        $preferences = App::make(PreferenceRepositoryInterface::class);

        $item = $preferences->make();
        $item->setKey('appearance.pages');
        $item->setPreferences([
            'theme' => 'oxygen/mod-pages::pages.view'
        ]);
        $preferences->persist($item, false);

        $item = $preferences->make();
        $item->setKey('modules.pages');
        $item->setPreferences([
            'cache' => [
                'enabled' => true,
                'location' => '/public/content/cache'
            ]
        ]);
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
