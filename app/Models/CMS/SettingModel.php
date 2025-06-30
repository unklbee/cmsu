<?php

namespace App\Models\CMS;

use App\Entities\CMS\SettingEntity;

class SettingModel extends BaseModel
{
    protected $table = 'cms_settings';
    protected $primaryKey = 'id';
    protected $returnType = SettingEntity::class;
    protected $allowedFields = [
        'key', 'value', 'type', 'group',
        'description', 'is_public'
    ];

    protected $validationRules = [
        'key' => 'required|string|is_unique[cms_settings.key,id,{id}]',
        'type' => 'required|in_list[string,number,json,boolean]',
        'group' => 'required|string'
    ];

    // Cache settings by group
    private static array $settingsCache = [];

    /**
     * Get setting value by key
     */
    public function get(string $key, $default = null)
    {
        $setting = $this->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->getParsedValue();
    }

    /**
     * Set setting value
     */
    public function setSetting(string $key, $value, array $additionalData = []): bool|int|string
    {
        $type = $this->detectType($value);
        $data = array_merge([
            'key' => $key,
            'value' => $this->prepareValue($value, $type),
            'type' => $type
        ], $additionalData);

        $existing = $this->where('key', $key)->first();

        if ($existing) {
            return $this->update($existing->id, $data);
        }

        return $this->insert($data);
    }

    /**
     * Get all settings by group
     */
    public function getByGroup(string $group, bool $publicOnly = false)
    {
        if (isset(self::$settingsCache[$group])) {
            return self::$settingsCache[$group];
        }

        $query = $this->where('group', $group);

        if ($publicOnly) {
            $query->where('is_public', 1);
        }

        $settings = $query->findAll();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getParsedValue();
        }

        self::$settingsCache[$group] = $result;

        return $result;
    }

    /**
     * Get all settings as key-value pairs
     */
    public function getAllSettings(bool $publicOnly = false): array
    {
        $query = $this;

        if ($publicOnly) {
            $query = $query->where('is_public', 1);
        }

        $settings = $query->findAll();
        $result = [];

        foreach ($settings as $setting) {
            if (!isset($result[$setting->group])) {
                $result[$setting->group] = [];
            }
            $result[$setting->group][$setting->key] = $setting->getParsedValue();
        }

        return $result;
    }

    /**
     * Bulk update settings
     */
    public function bulkSet(array $settings): bool
    {
        $success = true;

        foreach ($settings as $key => $value) {
            if (!$this->setSetting($key, $value)) {
                $success = false;
            }
        }

        $this->clearCache();

        return $success;
    }

    /**
     * Helper methods
     */
    private function detectType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        } elseif (is_numeric($value)) {
            return 'number';
        } elseif (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }

    private function prepareValue($value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string)$value,
        };
    }

    public function clearCache(): bool
    {
        self::$settingsCache = [];
        return parent::clearCache();
    }
}
