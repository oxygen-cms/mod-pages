<?php

namespace Oxygen\Pages;

use Illuminate\Support\ServiceProvider;

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

		// Blueprints
        $this->app['oxygen.blueprintManager']->loadDirectory(__DIR__ . '/../resources/blueprints');
        $this->app['oxygen.preferences']->loadDirectory(__DIR__ . '/../resources/preferences');

		// Extends Blade compiler
		$this->app['blade.compiler']->extend(function($view, $compiler) {
			$pattern = $compiler->createMatcher('partial');

			return preg_replace($pattern, '$1<?php echo $__env->model(Oxygen\Pages\Model\Partial::get($2), \'content\')->render(); ?>', $view);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */

	public function register() {
		//
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
