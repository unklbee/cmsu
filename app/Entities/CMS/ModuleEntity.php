<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;

class ModuleEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['installed_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'config' => 'json',
        'permissions' => 'json',
        'routes' => 'json',
        'order' => 'integer'
    ];

    /**
     * Check if module is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get module path
     */
    public function getPath(): string
    {
        return APPPATH . 'Modules/' . $this->name;
    }

    /**
     * Get config value
     */
    public function getConfig(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return dot_array_search($key, $this->config) ?? $default;
    }

    /**
     * Check if module has permission
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get module routes
     */
    public function getRoutes(): array
    {
        return $this->routes ?? [];
    }
}