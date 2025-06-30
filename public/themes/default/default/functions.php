<?php

/**
 * Theme Functions
 * File: public/themes/default/functions.php
 */
namespace Themes\Default;

class Functions
{
    /**
     * Theme setup
     */
    public static function setup()
    {
        // Add theme support
        self::addThemeSupport();

        // Register assets
        self::registerAssets();

        // Register widgets
        self::registerWidgets();
    }

    private static function addThemeSupport()
    {
        // Add support for post thumbnails
        add_theme_support('post-thumbnails');

        // Add support for title tag
        add_theme_support('title-tag');

        // Add support for HTML5
        add_theme_support('html5', ['search-form', 'comment-form', 'gallery']);
    }

    private static function registerAssets()
    {
        // Register CSS
        theme_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        theme_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        theme_style('theme-style', theme()->asset('css/style.css'), ['bootstrap']);

        // Register JS
        theme_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], true);
        theme_script('theme-script', theme()->asset('js/script.js'), ['bootstrap'], true);
    }

    private static function registerWidgets()
    {
        // Register widget areas
        register_widget_area([
            'id' => 'sidebar',
            'name' => 'Sidebar',
            'description' => 'Main sidebar widget area',
            'before_widget' => '<div class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widget-title">',
            'after_title' => '</h4>'
        ]);

        register_widget_area([
            'id' => 'footer',
            'name' => 'Footer',
            'description' => 'Footer widget area',
            'before_widget' => '<div class="col-md-3 widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h5 class="widget-title">',
            'after_title' => '</h5>'
        ]);
    }
}