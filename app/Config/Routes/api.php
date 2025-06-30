<?php

/**
 * API Routes Configuration
 * File: app/Config/Routes/api.php
 */

// API v1 Routes
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'cors'], function($routes) {

    // Public endpoints (no auth required)
    $routes->get('status', 'StatusController::index');
    $routes->get('settings/public', 'SettingsController::public');

    // Auth endpoints
    $routes->group('auth', function($routes) {
        $routes->post('login', 'AuthController::login');
        $routes->post('register', 'AuthController::register');
        $routes->post('forgot-password', 'AuthController::forgotPassword');
        $routes->post('reset-password', 'AuthController::resetPassword');
    });

    // Protected endpoints (require API auth)
    $routes->group('', ['filter' => 'apiAuth'], function($routes) {

        // User endpoints
        $routes->get('me', 'AuthController::me');
        $routes->put('me', 'AuthController::updateProfile');
        $routes->post('logout', 'AuthController::logout');

        // Content endpoints
        $routes->resource('posts', ['controller' => 'PostsController']);
        $routes->get('posts/(:segment)/comments', 'PostsController::comments/$1');
        $routes->post('posts/(:segment)/comments', 'PostsController::addComment/$1');

        // Media endpoints
        $routes->get('media', 'MediaController::index');
        $routes->post('media', 'MediaController::upload');
        $routes->get('media/(:num)', 'MediaController::show/$1');
        $routes->delete('media/(:num)', 'MediaController::delete/$1');

        // Settings endpoints (require admin permission)
        $routes->group('settings', ['filter' => 'apiAuth:admin.settings'], function($routes) {
            $routes->get('/', 'SettingsController::index');
            $routes->get('(:segment)', 'SettingsController::group/$1');
            $routes->put('/', 'SettingsController::update');
        });

        // Users endpoints (require admin permission)
        $routes->group('users', ['filter' => 'apiAuth:admin.users'], function($routes) {
            $routes->get('/', 'UsersController::index');
            $routes->get('(:num)', 'UsersController::show/$1');
            $routes->post('/', 'UsersController::create');
            $routes->put('(:num)', 'UsersController::update/$1');
            $routes->delete('(:num)', 'UsersController::delete/$1');
        });
    });
});