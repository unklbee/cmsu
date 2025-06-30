<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Shield\Entities\User;
use Config\Database;

/**
 * CMS Install Command
 * File: app/Commands/CMSInstall.php
 */
class CMSInstall extends BaseCommand
{
    protected $group = 'CMS';
    protected $name = 'cms:install';
    protected $description = 'Install CMS with initial setup';
    protected $usage = 'cms:install [options]';
    protected $arguments = [];
    protected $options = [
        '--force' => 'Force reinstall even if already installed',
    ];

    public function run(array $params)
    {
        CLI::write('CMS Installation', 'green');
        CLI::write('================', 'green');

        // Check if already installed
        $force = CLI::getOption('force') ?? false;

        if (!$force && $this->isInstalled()) {
            CLI::error('CMS is already installed. Use --force to reinstall.');
            return;
        }

        try {
            // Run migrations
            CLI::write('Running migrations...', 'yellow');
            $this->call('migrate', ['all']);

            // Run seeders
            CLI::write('Seeding database...', 'yellow');
            $this->call('db:seed', ['CMSSeeder']);

            // Create admin user
            $this->createAdminUser();

            // Set initial settings
            $this->setInitialSettings();

            // Create necessary directories
            $this->createDirectories();

            // Generate encryption key if needed
            if (empty($_ENV['encryption.key'])) {
                CLI::write('Generating encryption key...', 'yellow');
                $this->call('key:generate');
            }

            CLI::write('CMS installed successfully!', 'green');
            CLI::write('Admin URL: ' . site_url('admin'), 'blue');

        } catch (\Exception $e) {
            CLI::error('Installation failed: ' . $e->getMessage());
        }
    }

    private function isInstalled(): bool
    {
        $db = Database::connect();
        return $db->tableExists('users') && $db->tableExists('cms_settings');
    }

    private function createAdminUser(): void
    {
        CLI::write('Creating admin user...', 'yellow');

        // Check if admin already exists
        $users = auth()->getProvider();
        $db = Database::connect();

        // Check if any superadmin exists
        $existingSuperadmin = $db->table('auth_groups_users')
            ->where('group', 'superadmin')
            ->countAllResults();

        if ($existingSuperadmin > 0) {
            CLI::write('Superadmin user already exists, skipping...', 'yellow');
            return;
        }

        $username = CLI::prompt('Admin username', 'admin');
        $email = CLI::prompt('Admin email', 'admin@example.com');
        $password = CLI::prompt('Admin password (min 8 chars)', null, 'required');

        // Validate password length
        if (strlen($password) < 8) {
            CLI::error('Password must be at least 8 characters long');
            return;
        }

        $user = new User([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'active' => 1
        ]);

        $users->save($user);

        if ($users->errors()) {
            CLI::error('Failed to create admin user: ' . implode(', ', $users->errors()));
            return;
        }

        $userId = $users->getInsertID();

        // Add to superadmin group manually
        $db->table('auth_groups_users')->insert([
            'user_id' => $userId,
            'group' => 'superadmin',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Add all permissions for superadmin
        $permissions = [
            'admin.access', 'admin.settings', 'admin.users', 'admin.modules', 'admin.themes',
            'content.create', 'content.edit', 'content.delete', 'content.publish',
            'media.upload', 'media.delete', 'media.manage',
            'api.access', 'api.manage'
        ];

        foreach ($permissions as $permission) {
            $db->table('auth_permissions_users')->insert([
                'user_id' => $userId,
                'permission' => $permission,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        CLI::write('Admin user created successfully', 'green');
    }

    private function setInitialSettings(): void
    {
        $settings = model('App\Models\CMS\SettingModel');

        $defaultSettings = [
            'site_name' => CLI::prompt('Site name', 'My CMS'),
            'site_description' => CLI::prompt('Site description', 'A powerful CMS built with CodeIgniter 4'),
            'site_email' => CLI::prompt('Site email', 'admin@example.com'),
            'theme' => 'default',
            'posts_per_page' => 10,
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'timezone' => 'UTC',
            'maintenance_mode' => false
        ];

        foreach ($defaultSettings as $key => $value) {
            $settings->setSetting($key, $value);
        }
    }

    private function createDirectories(): void
    {
        $dirs = [
            WRITEPATH . 'uploads',
            WRITEPATH . 'uploads/media',
            WRITEPATH . 'cache/cms',
            WRITEPATH . 'logs/cms',
            FCPATH . 'uploads',
            FCPATH . 'themes'
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                CLI::write("Created directory: {$dir}", 'green');
            }
        }
    }
}
