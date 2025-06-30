<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;

class ActivityLogEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => '?integer',
        'model_id' => '?integer',
        'data' => 'json'
    ];

    /**
     * Get user
     */
    public function getUser()
    {
        if (!$this->user_id) {
            return null;
        }

        return model('UserModel')->find($this->user_id);
    }

    /**
     * Get action icon
     */
    public function getActionIcon(): string
    {
        $icons = [
            'create' => 'fa-plus text-success',
            'update' => 'fa-edit text-info',
            'delete' => 'fa-trash text-danger',
            'login' => 'fa-sign-in-alt text-primary',
            'logout' => 'fa-sign-out-alt text-secondary',
            'view' => 'fa-eye text-info'
        ];

        return $icons[$this->action] ?? 'fa-circle text-muted';
    }

    /**
     * Get formatted description
     */
    public function getFormattedDescription(): string
    {
        $user = $this->getUser();
        $userName = $user ? $user->username : 'System';

        return str_replace(
            ['{user}', '{action}', '{model}', '{id}'],
            [$userName, $this->action, class_basename($this->model ?? ''), $this->model_id ?? ''],
            $this->description
        );
    }
}