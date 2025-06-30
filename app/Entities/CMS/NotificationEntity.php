<?php

// Entity Files
namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class NotificationEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'read_at'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'data' => '?json-array'
    ];

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Get time ago
     */
    public function getTimeAgo(): string
    {
        return Time::parse($this->created_at)->humanize();
    }

    /**
     * Get icon for notification type
     */
    public function getIcon(): string
    {
        $icons = [
            'info' => 'fa-info-circle text-info',
            'success' => 'fa-check-circle text-success',
            'warning' => 'fa-exclamation-triangle text-warning',
            'error' => 'fa-times-circle text-danger',
            'user' => 'fa-user text-primary',
            'system' => 'fa-cog text-secondary'
        ];

        return $icons[$this->type] ?? 'fa-bell text-primary';
    }

    /**
     * Get data value
     */
    public function getData(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? $default;
    }
}