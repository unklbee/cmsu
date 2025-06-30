<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;

class SettingEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'is_public' => 'boolean',
        'id' => 'integer'
    ];

    /**
     * Get parsed value based on type
     */
    public function getParsedValue()
    {
        $value = $this->attributes['value'] ?? null;
        $type = $this->attributes['type'] ?? 'string';

        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : 0;
            case 'json':
                return json_decode($value, true) ?? [];
            default:
                return $value;
        }
    }

    /**
     * Set value with type conversion
     */
    public function setValue($value)
    {
        if (is_bool($value)) {
            $this->attributes['value'] = $value ? '1' : '0';
            $this->attributes['type'] = 'boolean';
        } elseif (is_numeric($value)) {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'number';
        } elseif (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
            $this->attributes['type'] = 'json';
        } else {
            $this->attributes['value'] = (string) $value;
            $this->attributes['type'] = 'string';
        }

        return $this;
    }
}