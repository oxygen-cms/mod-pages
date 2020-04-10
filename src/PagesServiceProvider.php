<?php

namespace OxygenModule\Pages;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Database\AutomaticMigrator;
use Oxygen\Data\BaseServiceProvider;
use Oxygen\Data\Cache\CacheSettingsRepositoryInterface;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use OxygenModule\Pages\Entity\Page;
use OxygenModule\Pages\Repository\DoctrinePageRepository;
use OxygenModule\Pages\Repository\DoctrinePartialRepository;
use OxygenModule\Pages\Repository\PageRepositoryInterface;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;

class PagesServiceProvider extends BaseServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'oxygen/mod-pages');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'oxygen/mod-pages');

        $this->publishes([
            __DIR__ . '/../resources/lang' => base_path('resources/lang/vendor/oxygen/mod-pages'),
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/oxygen/mod-pages')
        ]);

        // Blueprints
        $this->app[BlueprintManager::class]->loadDirectory(__DIR__ . '/../resources/blueprints');
        $this->app[PreferencesManager::class]->loadDirectory(__DIR__ . '/../resources/preferences');
        $this->app[AutomaticMigrator::class]->loadMigrationsFrom(__DIR__ . '/../migrations', 'oxygen/mod-pages');

        // Extends Blade compiler
        $this->app['blade.compiler']->directive('partial', function($expression) {
            $template = '<?php
                try {
                    $__item = app(\'' . PartialRepositoryInterface::class . '\')->findByKey(' . $expression . ');
                    if(method_exists($__env, \'viewDependsOnEntity\')) {
                        $__env->viewDependsOnEntity($__item);
                    }
                    echo $__env->model($__item, \'content\')->render();
                } catch (\Oxygen\Data\Exception\NoResultException $e) {
                    throw new \Exception("Partial ' . $expression . ' was not found", $e->getCode(), $e);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage(), $e->getCode(), $e);
                }
            ?>';
            // remove linebreaks from the template, because we want a 1:1 mapping for line numbers
            return str_replace(["\r", "\n"], '', $template);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->loadEntitiesFrom(__DIR__ . '/Entity');
        $this->app->bind(PageRepositoryInterface::class, DoctrinePageRepository::class);
        $this->app->bind(PartialRepositoryInterface::class, DoctrinePartialRepository::class);
    }

}
