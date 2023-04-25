<?php

use Illuminate\Routing\Router;
use OxygenModule\Pages\Controller\PagesController;
use OxygenModule\Pages\Controller\PartialsController;

$router = app('router');

$router->prefix('/oxygen/api')
    ->middleware('api_auth')
    ->group(function(Router $router) {

        // pages
        $router->prefix('pages')->group(function(Router $router) {
            PagesController::registerCrudRoutes($router);
            PagesController::registerSoftDeleteRoutes($router);
            PagesController::registerVersionableRoutes($router);
            PagesController::registerPublishableRoutes($router);
        });

        // partials
        $router->prefix('partials')->group(function(Router $router) {
            PartialsController::registerCrudRoutes($router);
            PartialsController::registerSoftDeleteRoutes($router);
            PartialsController::registerVersionableRoutes($router);
            PartialsController::registerPublishableRoutes($router);
        });

        $router->get('theme/details', [PagesController::class, 'getThemeDetails'])->name('pages.getThemeDetails');
    });
