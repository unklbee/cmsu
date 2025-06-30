<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Main CMS Seeder
 * File: app/Database/Seeds/CMSSeeder.php
 */
class CMSSeeder extends Seeder
{
    public function run()
    {
        // Create groups first (required by Shield)
        $this->createGroups();

        // Create permissions
        $this->createPermissions();

        // Create default users
        $this->createUsers();

        // Create default settings
        $this->createSettings();

        // Create default menus
        $this->createMenus();

        // Create default modules
        $this->createModules();

        // Run demo data seeder if in development
        if (ENVIRONMENT === 'development') {
            $this->createDemoData();
        }

        echo "CMS data seeded successfully.\n";
    }

    private function createGroups()
    {
        $db = \Config\Database::connect();

        // Check if groups already exist
        if ($db->table('auth_groups_users')->countAll() > 0) {
            echo "Groups already exist, skipping...\n";
            return;
        }

        // Shield doesn't have auth_groups table, it uses auth_groups_users directly
        // We'll define our groups here for reference
        $this->groups = [
            'superadmin' => 'Super Administrator',
            'admin' => 'Administrator',
            'editor' => 'Editor',
            'user' => 'User'
        ];

        echo "Group definitions set.\n";
    }

    private function createPermissions()
    {
        $db = \Config\Database::connect();

        // Check if permissions already exist
        if ($db->table('auth_permissions_users')->countAll() > 0) {
            echo "Permissions already exist, skipping...\n";
            return;
        }

        // Shield uses auth_permissions_users table
        // We'll define permissions for reference
        $this->permissions = [
            // System permissions
            'admin.access' => 'Access admin panel',
            'admin.settings' => 'Manage settings',
            'admin.users' => 'Manage users',
            'admin.modules' => 'Manage modules',
            'admin.themes' => 'Manage themes',

            // Content permissions
            'content.create' => 'Create content',
            'content.edit' => 'Edit content',
            'content.delete' => 'Delete content',
            'content.publish' => 'Publish content',

            // Media permissions
            'media.upload' => 'Upload media',
            'media.delete' => 'Delete media',
            'media.manage' => 'Manage all media',

            // API permissions
            'api.access' => 'Access API',
            'api.manage' => 'Manage API keys'
        ];

        echo "Permission definitions set.\n";
    }

    private function createUsers()
    {
        $users = auth()->getProvider();

        // Note: Admin user will be created by the install command
        // Here we just create sample users for development

        if (ENVIRONMENT === 'development') {
            try {
                $db = \Config\Database::connect();

                // Check if editor already exists
                $existingEditor = $db->table('users')->where('username', 'editor')->countAllResults();
                if ($existingEditor === 0) {
                    // Create editor user
                    $user = new \CodeIgniter\Shield\Entities\User([
                        'username' => 'editor',
                        'email' => 'editor@example.com',
                        'password' => 'password123',
                        'active' => 1
                    ]);

                    $users->save($user);
                    if (!$users->errors()) {
                        $userId = $users->getInsertID();

                        // Add to editor group using Shield's auth_groups_users table
                        $db->table('auth_groups_users')->insert([
                            'user_id' => $userId,
                            'group' => 'editor',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);

                        // Add permissions
                        $editorPermissions = [
                            'admin.access',
                            'content.create',
                            'content.edit',
                            'media.upload'
                        ];

                        foreach ($editorPermissions as $permission) {
                            $db->table('auth_permissions_users')->insert([
                                'user_id' => $userId,
                                'permission' => $permission,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }

                // Check if regular user already exists
                $existingUser = $db->table('users')->where('username', 'user')->countAllResults();
                if ($existingUser === 0) {
                    // Create regular user
                    $user = new \CodeIgniter\Shield\Entities\User([
                        'username' => 'user',
                        'email' => 'user@example.com',
                        'password' => 'password123',
                        'active' => 1
                    ]);

                    $users->save($user);
                    if (!$users->errors()) {
                        $userId = $users->getInsertID();

                        // Add to user group
                        $db->table('auth_groups_users')->insert([
                            'user_id' => $userId,
                            'group' => 'user',
                            'created_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }

                echo "Sample users created successfully.\n";
            } catch (\Exception $e) {
                echo "Error creating sample users: " . $e->getMessage() . "\n";
            }
        }
    }

    private function createSettings()
    {
        $settings = model('App\Models\CMS\SettingModel');

        // Check if settings already exist
        if ($settings->countAll() > 0) {
            echo "Settings already exist, skipping...\n";
            return;
        }

        $defaultSettings = [
            // General settings
            ['key' => 'site_name', 'value' => 'My CMS Site', 'type' => 'string', 'group' => 'general', 'is_public' => 1],
            ['key' => 'site_description', 'value' => 'A powerful CMS built with CodeIgniter 4', 'type' => 'string', 'group' => 'general', 'is_public' => 1],
            ['key' => 'site_keywords', 'value' => 'cms, codeigniter, php', 'type' => 'string', 'group' => 'general', 'is_public' => 1],
            ['key' => 'site_email', 'value' => 'admin@example.com', 'type' => 'string', 'group' => 'general'],

            // Theme settings
            ['key' => 'theme', 'value' => 'default', 'type' => 'string', 'group' => 'theme'],
            ['key' => 'admin_theme', 'value' => 'default', 'type' => 'string', 'group' => 'theme'],

            // System settings
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'system'],
            ['key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'group' => 'system'],
            ['key' => 'time_format', 'value' => 'H:i:s', 'type' => 'string', 'group' => 'system'],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'system'],

            // Media settings
            ['key' => 'upload_max_size', 'value' => '10', 'type' => 'number', 'group' => 'media'],
            ['key' => 'image_max_width', 'value' => '2000', 'type' => 'number', 'group' => 'media'],
            ['key' => 'image_max_height', 'value' => '2000', 'type' => 'number', 'group' => 'media'],

            // API settings
            ['key' => 'api_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'api'],
            ['key' => 'api_rate_limit', 'value' => '60', 'type' => 'number', 'group' => 'api']
        ];

        foreach ($defaultSettings as $setting) {
            $settings->insert($setting);
        }

        echo "Settings created successfully.\n";
    }

    private function createMenus()
    {
        $menus = model('App\Models\CMS\MenuModel');

        // Check if menus already exist
        if ($menus->countAll() > 0) {
            echo "Menus already exist, skipping...\n";
            return;
        }

        // Main menu
        $mainMenuItems = [
            ['menu_group' => 'main', 'title' => 'Home', 'url' => '/', 'icon' => 'fa-home', 'order' => 1],
            ['menu_group' => 'main', 'title' => 'About', 'url' => '/about', 'icon' => 'fa-info-circle', 'order' => 2],
            ['menu_group' => 'main', 'title' => 'Contact', 'url' => '/contact', 'icon' => 'fa-envelope', 'order' => 3]
        ];

        foreach ($mainMenuItems as $item) {
            $menus->insert($item);
        }

        // Admin menu
        $adminMenuItems = [
            ['menu_group' => 'admin', 'title' => 'Dashboard', 'url' => '/admin', 'icon' => 'fa-tachometer-alt', 'permission' => 'admin.access', 'order' => 1],
            ['menu_group' => 'admin', 'title' => 'Media', 'url' => '/admin/media', 'icon' => 'fa-images', 'permission' => 'media.upload', 'order' => 2],
            ['menu_group' => 'admin', 'title' => 'Users', 'url' => '/admin/users', 'icon' => 'fa-users', 'permission' => 'admin.users', 'order' => 3],
            ['menu_group' => 'admin', 'title' => 'Settings', 'url' => '/admin/settings', 'icon' => 'fa-cog', 'permission' => 'admin.settings', 'order' => 4]
        ];

        foreach ($adminMenuItems as $item) {
            $menus->insert($item);
        }

        echo "Menus created successfully.\n";
    }

    private function createModules()
    {
        $modules = model('App\Models\CMS\ModuleModel');

        // Check if modules already exist
        if ($modules->countAll() > 0) {
            echo "Modules already exist, skipping...\n";
            return;
        }

        // For now, we'll just register modules without installing them
        // Module installation should be done separately

        echo "Module registration skipped (install modules separately).\n";
    }

    private function createDemoData()
    {
        // Create sample notifications
        $notifications = model('App\Models\CMS\NotificationModel');

        if ($notifications->countAll() === 0) {
            $notifications->notify(1, 'info', 'Welcome to CMS', 'Your CMS has been installed successfully!');
            $notifications->notify(1, 'success', 'System Ready', 'All systems are operational.');

            echo "Demo data created successfully.\n";
        }
    }
}