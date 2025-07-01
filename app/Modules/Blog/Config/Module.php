<?php

/**
 * Blog Module Example
 * Location: app/Modules/Blog/
 */

// Module Configuration
// File: app/Modules/Blog/Config/Module.php
use Config\Database;
use Config\Services;

return [
    'name' => 'blog',
    'display_name' => 'Blog Module',
    'description' => 'A complete blog management system',
    'version' => '1.0.0',
    'author' => 'CMS Team',

    // Module configuration
    'config' => [
        'posts_per_page' => 10,
        'enable_comments' => true,
        'moderation_required' => true,
        'excerpt_length' => 200
    ],

    // Module permissions
    'permissions' => [
        'blog.view',
        'blog.create',
        'blog.edit',
        'blog.delete',
        'blog.publish',
        'blog.manage_comments'
    ],

    // Module routes
    'routes' => [
        ['from' => 'blog', 'to' => 'Blog::index'],
        ['from' => 'blog/post/(:segment)', 'to' => 'Blog::show/$1'],
        ['from' => 'blog/category/(:segment)', 'to' => 'Blog::category/$1'],
        ['from' => 'blog/tag/(:segment)', 'to' => 'Blog::tag/$1'],
        ['from' => 'admin/blog', 'to' => 'Admin\Blog::index'],
        ['from' => 'admin/blog/create', 'to' => 'Admin\Blog::create'],
        ['from' => 'admin/blog/edit/(:num)', 'to' => 'Admin\Blog::edit/$1']
    ],

    // Installation callback
    'install' => function() {
        // Run migrations using custom module migration service
        $moduleMigrationService = new \App\Services\CMS\ModuleMigrationService();

        try {
            $moduleMigrationService->migrateModule('Blog');
        } catch (\Exception $e) {
            log_message('error', 'Blog module migration failed: ' . $e->getMessage());
            return false;
        }

        // Create default category using raw query
        $db = Database::connect();

        // Check if category already exists
        $existingCategory = $db->table('blog_categories')
            ->where('slug', 'uncategorized')
            ->get()
            ->getRow();

        if (!$existingCategory) {
            $db->table('blog_categories')->insert([
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Add menu items (check if already exist)
        $existingMainMenu = $db->table('cms_menus')
            ->where('title', 'Blog')
            ->where('menu_group', 'main')
            ->get()
            ->getRow();

        if (!$existingMainMenu) {
            $db->table('cms_menus')->insert([
                'menu_group' => 'main',
                'title' => 'Blog',
                'url' => '/blog',
                'icon' => 'fa-newspaper',
                'order' => 10,
                'is_active' => 1,
                'metadata' => '{}',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $existingAdminMenu = $db->table('cms_menus')
            ->where('title', 'Blog')
            ->where('menu_group', 'admin')
            ->get()
            ->getRow();

        if (!$existingAdminMenu) {
            $db->table('cms_menus')->insert([
                'menu_group' => 'admin',
                'title' => 'Blog',
                'icon' => 'fa-newspaper',
                'permission' => 'blog.view',
                'order' => 10,
                'is_active' => 1,
                'metadata' => '{}',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        return true;
    },

    // Uninstall callback
    'uninstall' => function() {
        // Remove menu items
        $db = Database::connect();
        $db->table('cms_menus')->where('title', 'Blog')->delete();

        // Rollback migrations
        $moduleMigrationService = new \App\Services\CMS\ModuleMigrationService();
        $moduleMigrationService->rollbackModule('Blog');

        return true;
    }
];