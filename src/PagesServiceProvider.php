<?php

namespace Oxygen\Pages;

use Oxygen\Core\Support\ServiceProvider;

class PagesServiceProvider extends ServiceProvider {

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
		$this->package('oxygen/pages', 'oxygen/pages', __DIR__ . '/../resources');
        $this->entities(__DIR__ . '/Entity');

		// Blueprints
        $this->app['oxygen.blueprintManager']->loadDirectory(__DIR__ . '/../resources/blueprints');
        $this->app['oxygen.preferences']->loadDirectory(__DIR__ . '/../resources/preferences');

		// Extends Blade compiler
		$this->app['blade.compiler']->extend(function($view, $compiler) {
			$pattern = $compiler->createMatcher('partial');

			return preg_replace($pattern, '$1<?php echo $__env->model($app[\'Oxygen\Pages\Repository\PartialRepositoryInterface\']->findByKey($2), \'content\')->render(); ?>', $view);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		$this->app->bind('Oxygen\Pages\Repository\PageRepositoryInterface', 'Oxygen\Pages\Repository\DoctrinePageRepository');
        $this->app->bind('Oxygen\Pages\Repository\PartialRepositoryInterface', 'Oxygen\Pages\Repository\DoctrinePartialRepository');
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
