<?php

namespace OxygenModule\Pages;

use Oxygen\Core\Blueprint\BlueprintManager;
use Oxygen\Core\Content\ObjectLinkRegistry;
use Oxygen\Core\Templating\DoctrineResourceLoader;
use Oxygen\Core\Templating\TwigTemplateCompiler;
use Oxygen\Data\BaseServiceProvider;
use Oxygen\Preferences\PreferencesManager;
use OxygenModule\Pages\Console\ConvertPageContent;
use OxygenModule\Pages\Repository\DoctrinePageRepository;
use OxygenModule\Pages\Repository\DoctrinePartialRepository;
use OxygenModule\Pages\Repository\PageRepositoryInterface;
use OxygenModule\Pages\Repository\PersonRepositoryInterface;
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
        $this->loadRoutesFrom(__DIR__ . '/../resources/routes.php');

        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/oxygen/mod-pages'),
            __DIR__ . '/../resources/views' => base_path('resources/views/vendor/oxygen/mod-pages')
        ]);

        $this->commands(ConvertPageContent::class);

        // Blueprints
        $this->app[BlueprintManager::class]->loadDirectory(__DIR__ . '/../resources/blueprints');
        $this->app[PreferencesManager::class]->loadDirectory(__DIR__ . '/../resources/preferences');
        $this->loadMigrationsFrom(__DIR__ . '/../migrations');

        // Extends Blade compiler
        $this->app['blade.compiler']->directive('partial', function($expression) {
            $template = '<?php
                try {
                    $__item = app(\'' . PartialRepositoryInterface::class . '\')->findByKey(' . $expression . ');
                    echo app(\Oxygen\Core\Templating\TwigTemplateCompiler::class)->render($__item);
                } catch(\Oxygen\Data\Exception\NoResultException $e) {
                    echo "partial not found";
                } 
            ?>';

            // remove linebreaks from the template, because we want a 1:1 mapping for line numbers
            return str_replace(["\r", "\n"], '', $template);
        });

        $this->app->resolving(TwigTemplateCompiler::class, function(TwigTemplateCompiler $c, $app) {
            $c->getLoader()->addResourceType('pages', new DoctrineResourceLoader($app, PageRepositoryInterface::class, function(DoctrineResourceLoader $loader, string $key) {
                return false;
            }));
            $c->getLoader()->addResourceType('partials', new DoctrineResourceLoader($app, PartialRepositoryInterface::class, function(DoctrineResourceLoader $loader, string $key) {
                $partial = $loader->getByKey($key);
                return '<div data-partial-id="' . $partial->getId() . '"></div>';
            }));
        });

        $this->app[ObjectLinkRegistry::class]->addType(new PageLinkType($this->app[PageRepositoryInterface::class]));
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
