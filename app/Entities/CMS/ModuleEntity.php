<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;

class ModuleEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['installed_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'config' => 'json-array',      // Pastikan ada -array
        'permissions' => 'json-array',  // Pastikan ada -array
        'routes' => 'json-array',       // Pastikan ada -array
        'order' => 'integer'
    ];

    /**
     * Check if module is active
     */
    public function isActive(): bool
    {
        return $this->attributes['status'] === 'active';
    }

    /**
     * Get module path
     */
    public function getPath(): string
    {
        return APPPATH . 'Modules/' . $this->attributes['name'];
    }

    /**
     * Get config value
     */
    public function getConfig(string $key = null, $default = null)
    {
        $config = $this->attributes['config'] ?? [];

        // Handle jika config masih string JSON
        if (is_string($config)) {
            $config = json_decode($config, true) ?? [];
        }

        if ($key === null) {
            return $config;
        }

        return dot_array_search($key, $config) ?? $default;
    }

    /**
     * Check if module has permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->attributes['permissions'] ?? [];

        // Handle jika permissions masih string JSON
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        }

        return in_array($permission, $permissions);
    }

    /**
     * Get module routes
     */
    public function getRoutes(): array
    {
        $routes = $this->attributes['routes'] ?? [];

        // Handle jika routes masih string JSON
        if (is_string($routes)) {
            $routes = json_decode($routes, true) ?? [];
        }

        return $routes;
    }
}