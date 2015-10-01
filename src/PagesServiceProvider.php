<?php

namespace OxygenModule\Pages;

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Database\AutomaticMigrator;
use Oxygen\Data\BaseServiceProvider;
use Oxygen\Preferences\PreferenceNotFoundException;
use Oxygen\Preferences\PreferencesManager;
use OxygenModule\Pages\Cache\CacheInterface;
use OxygenModule\Pages\Cache\CacheInvalidationInterface;
use OxygenModule\Pages\Cache\CacheMiddleware;
use OxygenModule\Pages\Cache\FileCache;
use OxygenModule\Pages\Cache\PageCacheInvalidation;
use OxygenModule\Pages\Cache\PageCacheSubscriber;
use OxygenModule\Pages\Entity\Page;
use OxygenModule\Pages\Repository\DoctrinePageRepository;
use OxygenModule\Pages\Repository\DoctrinePartialRepository;
use OxygenModule\Pages\Repository\PageRepositoryInterface;
use OxygenModule\Pages\Repository\PartialRepositoryInterface;

class PagesServiceProvider extends BaseServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */

    protected $defer = false;

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
            return '<?php $__item = app(\'' . PartialRepositoryInterface::class . '\')->findByKey' . $expression . ';
            if(method_exists($__env, \'viewDependsOnEntity\')) {
                $__env->viewDependsOnEntity($__item);
            }
            echo $__env->model($__item, \'content\')->render(); ?>';
        });

        try {
            if($this->app[PreferencesManager::class]->get('modules.pages::cache.enabled') === true) {
                $this->app['router']->middleware('oxygen.cache', CacheMiddleware::class);
                $function = function($entities) {
                    $entities->getEventManager()
                             ->addEventSubscriber(app(PageCacheSubscriber::class));
                };
                if($this->app->resolved(EntityManager::class)) {
                    $function($this->app[EntityManager::class]);
                } else {
                    $this->app->resolving(EntityManager::class, $function);
                }
            }
        } catch(PreferenceNotFoundException $e) {
            // we don't cache
        }

        $this->app['events']->listen('oxygen.entity.cache.invalidated', function($entity) {
            if($entity instanceof Page && $entity->isPublished()) {
                $this->app[CacheInterface::class]->clear($entity->getSlug());
            }
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

        // Page Caching
        $this->app->bind(CacheInterface::class, FileCache::class);
        $this->app->singleton(FileCache::class, function($app) {
            return new FileCache(base_path() . '/' . $app[PreferencesManager::class]->get('modules.pages::cache.location'), $app['files']);
        });
        $this->app->singleton(PageCacheSubscriber::class, function($app) {
            return new PageCacheSubscriber($app['view.engine.resolver'], $app['view.finder'], $app['events']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [];
    }

}
