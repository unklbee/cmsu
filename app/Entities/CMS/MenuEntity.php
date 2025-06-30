<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;

class MenuEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'parent_id' => '?integer',
        'is_active' => 'boolean',
        'order' => 'integer',
        'metadata' => 'json'
    ];

    public $children = [];

    /**
     * Get URL for menu item
     */
    public function getUrl(): string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->route_name) {
            return route_to($this->route_name);
        }

        return '#';
    }

    /**
     * Check if menu has children
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }

    /**
     * Get metadata value
     */
    public function getMeta(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Check if menu is active based on current URL
     */
    public function isActive(): bool
    {
        $currentUrl = current_url();
        $menuUrl = $this->getUrl();

        if ($menuUrl === '#') {
            return false;
        }

        // Exact match
        if ($currentUrl === site_url($menuUrl)) {
            return true;
        }

        // Check if current URL starts with menu URL
        if (strpos($currentUrl, site_url($menuUrl)) === 0) {
            return true;
        }

        // Check children
        foreach ($this->children as $child) {
            if ($child->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get icon HTML
     */
    public function getIcon(): string
    {
        if (!$this->icon) {
            return '';
        }

        // Support for FontAwesome, Material Icons, etc
        if (strpos($this->icon, 'fa-') === 0) {
            return '<i class="fas ' . $this->icon . '"></i>';
        }

        if (strpos($this->icon, 'material-') === 0) {
            return '<i class="material-icons">' . str_replace('material-', '', $this->icon) . '</i>';
        }

        return '<i class="' . $this->icon . '"></i>';
    }
}