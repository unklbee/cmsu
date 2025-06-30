<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Home routes (place before Shield routes)
$routes->GET('/', 'Home::index');
$routes->GET('about', 'Home::about');
$routes->GET('contact', 'Home::contact');
$routes->POST('contact/submit', 'Home::submitContact');

// Custom auth routes (MUST be before Shield routes)
$routes->GET('login', 'AuthPages::login', ['as' => 'login']);
$routes->POST('login', 'Auth::loginAction');
$routes->GET('logout', 'Auth::logoutAction', ['as' => 'logout']);
$routes->GET('register', 'AuthPages::register', ['as' => 'register']);
$routes->POST('register', '\CodeIgniter\Shield\Controllers\RegisterController::registerAction');
$routes->GET('forgot-password', 'AuthPages::forgotPassword');

// Admin routes
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'adminAuth'], function($routes) {
    // Dashboard
    $routes->GET('/', 'Dashboard::index');
    $routes->GET('dashboard', 'Dashboard::index');

    // Media
    $routes->GET('media', 'Media::index');
    $routes->POST('media/upload', 'Media::upload');
    $routes->delete('media/(:num)', 'Media::delete/$1');

    // Users
    $routes->GET('users', 'Users::index');
    $routes->GET('users/create', 'Users::create');
    $routes->POST('users/store', 'Users::store');
    $routes->GET('users/edit/(:num)', 'Users::edit/$1');
    $routes->POST('users/update/(:num)', 'Users::update/$1');
    $routes->delete('users/(:num)', 'Users::delete/$1');

    // Settings
    $routes->GET('settings', 'Settings::index');
    $routes->match(['GET', 'POST'], 'settings/general', 'Settings::general');
    $routes->match(['get', 'post'], 'settings/email', 'Settings::email');
    $routes->match(['get', 'post'], 'settings/media', 'Settings::media');
    $routes->match(['get', 'post'], 'settings/seo', 'Settings::seo');
    $routes->match(['get', 'post'], 'settings/social', 'Settings::social');

    // Modules
    $routes->GET('modules', 'Modules::index');
    $routes->POST('modules/toggle/(:segment)', 'Modules::toggle/$1');
    $routes->GET('modules/install/(:segment)', 'Modules::install/$1');
    $routes->GET('modules/uninstall/(:num)', 'Modules::uninstall/$1');
    $routes->GET('modules/config/(:num)', 'Modules::config/$1');

    // Notifications
    $routes->GET('notifications', 'Notifications::index');
    $routes->put('notifications/(:num)/read', 'Notifications::markAsRead/$1');
    $routes->POST('notifications/mark-all-read', 'Notifications::markAllAsRead');
});

// API routes
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\V1', 'filter' => 'cors'], function($routes) {
    // Include API routes here
});

// Shield routes - MUST be at the end and exclude our custom routes
service('auth')->routes($routes, ['except' => ['login', 'logout', 'register']]);


/**
 * .htaccess Configuration
 * File: public/.htaccess
 *
 * For removing index.php from URL
 */

/*
# Disable directory browsing
Options -Indexes

# ----------------------------------------------------------------------
# Rewrite engine
# ----------------------------------------------------------------------

# Turning on the rewrite engine is necessary for the following rules and features.
# FollowSymLinks must be enabled for this to work.
<IfModule mod_rewrite.c>
    Options +FollowSymlinks
    RewriteEngine On

    # If you installed CodeIgniter in a subfolder, you will need to
    # change the following line to match the subfolder you need.
    # http://httpd.apache.org/docs/current/mod/mod_rewrite.html#rewritebase
    # RewriteBase /

    # Redirect Trailing Slashes...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Rewrite "www.example.com -> example.com"
    RewriteCond %{HTTPS} !=on
    RewriteCond %{HTTP_HOST} ^www\.(.+)$ [NC]
    RewriteRule ^ http://%1%{REQUEST_URI} [R=301,L]

    # Checks to see if the user is attempting to access a valid file,
    # such as an image or css document, if this isn't true it sends the
    # request to the front controller, index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^([\s\S]*)$ index.php/$1 [L,NC,QSA]

    # Ensure Authorization header is passed along
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
</IfModule>

<IfModule !mod_rewrite.c>
    # If we don't have mod_rewrite installed, all 404's
    # can be sent to index.php, and everything works as normal.
    ErrorDocument 404 index.php
</IfModule>

# Disable server signature
ServerSignature Off
*/