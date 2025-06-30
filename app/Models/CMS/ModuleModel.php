<?php

namespace App\Models\CMS;

use App\Entities\CMS\ModuleEntity;
use CodeIgniter\Files\File;
use Exception;

class ModuleModel extends BaseModel
{
    protected $table = 'cms_modules';
    protected $primaryKey = 'id';
    protected $returnType = ModuleEntity::class;
    protected $allowedFields = [
        'name', 'display_name', 'description', 'version',
        'author', 'status', 'config', 'permissions',
        'routes', 'order', 'installed_at'
    ];

    protected $validationRules = [
        'name' => 'required|string|is_unique[cms_modules.name,id,{id}]|regex_match[/^[a-z0-9_]+$/]',
        'display_name' => 'required|string',
        'status' => 'required|in_list[active,inactive,error]'
    ];

    protected array $casts = [
        'config' => 'json-array',      // Tambahkan -array
        'permissions' => 'json-array', // Tambahkan -array
        'routes' => 'json-array'       // Tambahkan -array
    ];

    private string $modulesPath;

    public function __construct()
    {
        parent::__construct();
        $this->modulesPath = APPPATH . 'Modules/';
    }

    /**
     * Get active modules
     */
    public function getActiveModules(): array
    {
        return $this->where('status', 'active')
            ->orderBy('order', 'ASC')
            ->findAll();
    }

    /**
     * Install module
     * @throws Exception
     */
    public function installModule(string $moduleName): bool
    {
        $modulePath = $this->modulesPath . $moduleName;

        if (!is_dir($modulePath)) {
            throw new Exception("Module directory not found: {$moduleName}");
        }

        // Load module config
        $configFile = $modulePath . '/Config/Module.php';
        if (!file_exists($configFile)) {
            throw new Exception("Module config not found: {$configFile}");
        }

        $config = require $configFile;

        // Validate required config
        if (!isset($config['name'], $config['display_name'], $config['version'])) {
            throw new Exception("Invalid module config");
        }

        // Check if already installed
        if ($this->where('name', $config['name'])->first()) {
            throw new Exception("Module already installed: {$config['name']}");
        }

        // Run module installation
        if (isset($config['install']) && is_callable($config['install'])) {
            if (!$config['install']()) {
                throw new Exception("Module installation failed");
            }
        }

        // Save module info
        $data = [
            'name' => $config['name'],
            'display_name' => $config['display_name'],
            'description' => $config['description'] ?? null,
            'version' => $config['version'],
            'author' => $config['author'] ?? null,
            'status' => 'inactive',
            'config' => $config['config'] ?? [],
            'permissions' => $config['permissions'] ?? [],
            'routes' => $config['routes'] ?? [],
            'order' => $this->getNextOrder(),
            'installed_at' => date('Y-m-d H:i:s')
        ];

        if (!$this->insert($data)) {
            throw new Exception("Failed to save module info");
        }

        // Register permissions
        if (!empty($config['permissions'])) {
            $this->registerPermissions($config['name'], $config['permissions']);
        }

        return true;
    }

    /**
     * Uninstall module
     */
    public function uninstallModule(string $moduleName): bool
    {
        $module = $this->where('name', $moduleName)->first();

        if (!$module) {
            throw new Exception("Module not found: {$moduleName}");
        }

        // Run module uninstall
        $modulePath = $this->modulesPath . $moduleName;
        $configFile = $modulePath . '/Config/Module.php';

        if (file_exists($configFile)) {
            $config = require $configFile;

            if (isset($config['uninstall']) && is_callable($config['uninstall'])) {
                if (!$config['uninstall']()) {
                    throw new Exception("Module uninstallation failed");
                }
            }
        }

        // Remove permissions
        $this->removePermissions($moduleName);

        // Delete module record
        return $this->delete($module->id);
    }

    /**
     * Activate/Deactivate module
     * @throws Exception
     */
    public function toggleModule(string $moduleName): bool
    {
        $module = $this->where('name', $moduleName)->first();

        if (!$module) {
            throw new Exception("Module not found: {$moduleName}");
        }

        $newStatus = $module->status === 'active' ? 'inactive' : 'active';

        return $this->update($module->id, ['status' => $newStatus]);
    }

    /**
     * Update module configuration
     * @throws Exception
     */
    public function updateConfig(string $moduleName, array $config): bool
    {
        $module = $this->where('name', $moduleName)->first();

        if (!$module) {
            throw new Exception("Module not found: {$moduleName}");
        }

        $currentConfig = $module->config ?? [];
        $newConfig = array_merge($currentConfig, $config);

        return $this->update($module->id, ['config' => $newConfig]);
    }

    /**
     * Check for module updates
     */
    public function checkUpdates(string $moduleName): ?array
    {
        $module = $this->where('name', $moduleName)->first();

        if (!$module) {
            return null;
        }

        $modulePath = $this->modulesPath . $moduleName;
        $configFile = $modulePath . '/Config/Module.php';

        if (!file_exists($configFile)) {
            return null;
        }

        $config = require $configFile;

        if (version_compare($config['version'], $module->version, '>')) {
            return [
                'current_version' => $module->version,
                'new_version' => $config['version'],
                'changelog' => $config['changelog'] ?? null
            ];
        }

        return null;
    }

    /**
     * Update module
     * @throws Exception
     */
    public function updateModule(string $moduleName): bool
    {
        $updateInfo = $this->checkUpdates($moduleName);

        if (!$updateInfo) {
            return false;
        }

        $module = $this->where('name', $moduleName)->first();
        $modulePath = $this->modulesPath . $moduleName;
        $configFile = $modulePath . '/Config/Module.php';
        $config = require $configFile;

        // Run update script
        if (isset($config['update']) && is_callable($config['update'])) {
            if (!$config['update']($module->version, $config['version'])) {
                throw new Exception("Module update failed");
            }
        }

        // Update module info
        return $this->update($module->id, [
            'version' => $config['version'],
            'config' => $config['config'] ?? $module->config,
            'permissions' => $config['permissions'] ?? $module->permissions,
            'routes' => $config['routes'] ?? $module->routes
        ]);
    }

    /**
     * Helper methods
     */
    private function getNextOrder(): int
    {
        $lastModule = $this->orderBy('order', 'DESC')->first();
        return $lastModule ? $lastModule->order + 1 : 1;
    }

    private function registerPermissions(string $moduleName, array $permissions): void
    {
        // Implementation depends on your permission system
        // This is a placeholder
        foreach ($permissions as $permission) {
            // Register permission in Shield or your auth system
        }
    }

    private function removePermissions(string $moduleName)
    {
        // Implementation depends on your permission system
        // This is a placeholder
    }
}