<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class ApiKeyEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['expires_at', 'last_used_at', 'created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'permissions' => 'json',
        'rate_limit' => 'integer',
        'is_active' => 'boolean'
    ];

    // Temporary property for returning plain secret
    public $plain_secret;

    /**
     * Check if key is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return Time::parse($this->expires_at)->isPast();
    }

    /**
     * Check if has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (in_array('*', $this->permissions ?? [])) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpiration(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        $now = Time::now();
        $expires = Time::parse($this->expires_at);

        if ($expires->isPast()) {
            return 0;
        }

        return $now->difference($expires)->getDays();
    }

    /**
     * Get last used ago
     */
    public function getLastUsedAgo(): ?string
    {
        if (!$this->last_used_at) {
            return 'Never';
        }

        return Time::parse($this->last_used_at)->humanize();
    }

    /**
     * Get status
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'revoked';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge(): string
    {
        $status = $this->getStatus();
        $classes = [
            'active' => 'badge-success',
            'expired' => 'badge-warning',
            'revoked' => 'badge-danger'
        ];

        $class = $classes[$status] ?? 'badge-secondary';

        return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
    }
}