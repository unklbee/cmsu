<?php

namespace App\Services\CMS;

use Config\Services;

/**
 * CMS Service - Main service for CMS operations
 */
class CMSService
{
    protected $settings;
    protected $modules;
    protected $menus;
    protected $cache;

    public function __construct()
    {
        $this->settings = model('App\Models\CMS\SettingModel');
        $this->modules = model('App\Models\CMS\ModuleModel');
        $this->menus = model('App\Models\CMS\MenuModel');
        $this->cache = Services::cache();
    }

    /**
     * Initialize CMS
     */
    public function initialize(): void
    {
        // Load active modules
        $this->loadModules();

        // Register module routes
        $this->registerModuleRoutes();

        // Set global data
        $this->setGlobalData();

        // Run module hooks
        $this->runHook('cms_init');
    }

    /**
     * Load active modules
     */
    protected function loadModules(): void
    {
        $modules = $this->modules->getActiveModules();

        foreach ($modules as $module) {
            $modulePath = APPPATH . 'Modules/' . $module->name;
            $bootstrapFile = $modulePath . '/Bootstrap.php';

            if (file_exists($bootstrapFile)) {
                require_once $bootstrapFile;

                $bootstrapClass = '\\App\\Modules\\' . ucfirst($module->name) . '\\Bootstrap';
                if (class_exists($bootstrapClass)) {
                    $bootstrap = new $bootstrapClass();
                    if (method_exists($bootstrap, 'init')) {
                        $bootstrap->init();
                    }
                }
            }
        }
    }

    /**
     * Register module routes
     */
    protected function registerModuleRoutes(): void
    {
        $router = Services::router();
        $modules = $this->modules->getActiveModules();

        foreach ($modules as $module) {
            if (!empty($module->routes)) {
                foreach ($module->routes as $route) {
                    if (isset($route['from'], $route['to'])) {
                        $router->add($route['from'], $route['to'], $route['options'] ?? []);
                    }
                }
            }
        }
    }

    /**
     * Set global template data
     */
    protected function setGlobalData(): void
    {
        $renderer = Services::renderer();

        // Site settings
        $renderer->setData([
            'site_name' => $this->getSetting('site_name', 'CMS'),
            'site_description' => $this->getSetting('site_description'),
            'site_logo' => $this->getSetting('site_logo'),
            'site_favicon' => $this->getSetting('site_favicon'),
            'theme' => $this->getSetting('theme', 'default'),
            'cms_version' => '1.0.0'
        ]);
    }

    /**
     * Get setting value
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings->get($key, $default);
    }

    /**
     * Get all settings by group
     */
    public function getSettings(string $group = null): array
    {
        if ($group) {
            return $this->settings->getByGroup($group);
        }

        return $this->settings->getAllSettings();
    }

    /**
     * Get menu tree
     */
    public function getMenu(string $group = 'main', bool $activeOnly = true): array
    {
        return $this->menus->getMenuTree($group, $activeOnly);
    }

    /**
     * Run module hooks
     */
    public function runHook(string $hook, array $params = []): array
    {
        $results = [];
        $modules = $this->modules->getActiveModules();

        foreach ($modules as $module) {
            $hookClass = '\\App\\Modules\\' . ucfirst($module->name) . '\\Hooks';

            if (class_exists($hookClass) && method_exists($hookClass, $hook)) {
                $hookInstance = new $hookClass();
                $results[$module->name] = $hookInstance->$hook($params);
            }
        }

        return $results;
    }

    /**
     * Check if module is active
     */
    public function isModuleActive(string $moduleName): bool
    {
        $module = $this->modules->where('name', $moduleName)->first();
        return $module && $module->isActive();
    }

    /**
     * Get theme path
     */
    public function getThemePath(string $file = ''): string
    {
        $theme = $this->getSetting('theme', 'default');
        return 'themes/' . $theme . '/' . $file;
    }
}