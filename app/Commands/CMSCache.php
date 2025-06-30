<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;


/**
 * Cache Management Command
 * File: app/Commands/CMSCache.php
 */
class CMSCache extends BaseCommand
{
    protected $group = 'CMS';
    protected $name = 'cms:cache';
    protected $description = 'Manage CMS cache';
    protected $usage = 'cms:cache [action]';
    protected $arguments = [
        'action' => 'Action to perform: clear, warm, status'
    ];

    public function run(array $params)
    {
        $action = $params[0] ?? 'status';

        switch ($action) {
            case 'clear':
                $this->clearCache();
                break;

            case 'warm':
                $this->warmCache();
                break;

            case 'status':
                $this->showStatus();
                break;

            default:
                CLI::error("Unknown action: {$action}");
        }
    }

    private function clearCache(): void
    {
        CLI::write('Clearing CMS cache...', 'yellow');

        // Clear CodeIgniter cache
        cache()->clean();

        // Clear CMS specific cache
        $cacheService = service('cmsCache');
        $tags = ['cms', 'settings', 'menus', 'modules', 'blog'];

        foreach ($tags as $tag) {
            $cacheService->flushTag($tag);
            CLI::write("Cleared cache tag: {$tag}", 'green');
        }

        // Clear view cache
        $viewCache = WRITEPATH . 'cache/';
        if (is_dir($viewCache)) {
            foreach (glob($viewCache . '*.php') as $file) {
                unlink($file);
            }
        }

        CLI::write('Cache cleared successfully!', 'green');
    }

    private function warmCache(): void
    {
        CLI::write('Warming up cache...', 'yellow');

        // Cache settings
        $settings = model('App\Models\CMS\SettingModel');
        $settings->getAllSettings();
        CLI::write('Cached settings', 'green');

        // Cache menus
        $menus = model('App\Models\CMS\MenuModel');
        $menuGroups = $menus->getGroups();
        foreach ($menuGroups as $group) {
            $menus->getMenuTree($group->menu_group);
        }
        CLI::write('Cached menus', 'green');

        // Cache modules
        $modules = model('App\Models\CMS\ModuleModel');
        $modules->getActiveModules();
        CLI::write('Cached modules', 'green');

        CLI::write('Cache warmed up successfully!', 'green');
    }

    private function showStatus(): void
    {
        $cache = cache();
        $cacheInfo = $cache->getCacheInfo();

        CLI::write('Cache Status', 'green');
        CLI::write('============', 'green');

        if (empty($cacheInfo)) {
            CLI::write('No cache information available', 'yellow');
            return;
        }

        $tbody = [];
        foreach ($cacheInfo as $key => $value) {
            $tbody[] = [$key, $value];
        }

        CLI::table($tbody, ['Key', 'Value']);
    }
}