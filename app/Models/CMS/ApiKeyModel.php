<?php

namespace App\Models\CMS;

use App\Entities\CMS\ApiKeyEntity;
use CodeIgniter\I18n\Time;

class ApiKeyModel extends BaseModel
{
    protected $table = 'cms_api_keys';
    protected $primaryKey = 'id';
    protected $returnType = ApiKeyEntity::class;
    protected $allowedFields = [
        'user_id', 'name', 'key', 'secret', 'permissions',
        'rate_limit', 'expires_at', 'last_used_at', 'is_active'
    ];

    protected $validationRules = [
        'user_id' => 'required|numeric',
        'name' => 'required|string',
        'key' => 'required|string|is_unique[cms_api_keys.key,id,{id}]',
        'rate_limit' => 'required|numeric|greater_than[0]'
    ];

    protected $casts = [
        'permissions' => 'json',
        'is_active' => 'boolean',
        'rate_limit' => 'integer'
    ];

    /**
     * Generate new API key
     */
    public function generateKey(int $userId, string $name, array $options = []): ?ApiKeyEntity
    {
        $key = $this->generateSecureKey();
        $secret = $this->generateSecureSecret();

        $data = [
            'user_id' => $userId,
            'name' => $name,
            'key' => $key,
            'secret' => password_hash($secret, PASSWORD_BCRYPT),
            'permissions' => $options['permissions'] ?? [],
            'rate_limit' => $options['rate_limit'] ?? 60,
            'expires_at' => $options['expires_at'] ?? null,
            'is_active' => $options['is_active'] ?? true
        ];

        $id = $this->insert($data);

        if ($id) {
            $apiKey = $this->find($id);
            // Return with plain secret only this time
            $apiKey->plain_secret = $secret;
            return $apiKey;
        }

        return null;
    }

    /**
     * Validate API key and secret
     */
    public function validate(string $key, string $secret): ?ApiKeyEntity
    {
        $apiKey = $this->where('key', $key)->first();

        if (!$apiKey || !$apiKey->is_active) {
            return null;
        }

        // Check expiration
        if ($apiKey->expires_at && Time::parse($apiKey->expires_at)->isPast()) {
            return null;
        }

        // Verify secret
        if (!password_verify($secret, $apiKey->secret)) {
            return null;
        }

        // Update last used
        $this->update($apiKey->id, ['last_used_at' => Time::now()]);

        return $apiKey;
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit(string $key, string $identifier = null): bool
    {
        $apiKey = $this->where('key', $key)->first();

        if (!$apiKey) {
            return false;
        }

        $identifier = $identifier ?? $key;
        $cacheKey = 'api_rate_limit_' . md5($identifier);
        $cache = cache();

        $current = $cache->get($cacheKey) ?? 0;

        if ($current >= $apiKey->rate_limit) {
            return false;
        }

        $cache->save($cacheKey, $current + 1, 60); // 1 minute window

        return true;
    }

    /**
     * Get remaining rate limit
     */
    public function getRemainingLimit(string $key, string $identifier = null): int
    {
        $apiKey = $this->where('key', $key)->first();

        if (!$apiKey) {
            return 0;
        }

        $identifier = $identifier ?? $key;
        $cacheKey = 'api_rate_limit_' . md5($identifier);
        $current = cache($cacheKey) ?? 0;

        return max(0, $apiKey->rate_limit - $current);
    }

    /**
     * Check permission
     */
    public function hasPermission(string $key, string $permission): bool
    {
        $apiKey = $this->where('key', $key)->first();

        if (!$apiKey || !$apiKey->is_active) {
            return false;
        }

        // Check if has wildcard permission
        if (in_array('*', $apiKey->permissions ?? [])) {
            return true;
        }

        // Check specific permission
        return in_array($permission, $apiKey->permissions ?? []);
    }

    /**
     * Revoke API key
     */
    public function revoke(int $id): bool
    {
        return $this->update($id, ['is_active' => false]);
    }

    /**
     * Regenerate secret
     */
    public function regenerateSecret(int $id): ?string
    {
        $newSecret = $this->generateSecureSecret();

        $updated = $this->update($id, [
            'secret' => password_hash($newSecret, PASSWORD_BCRYPT)
        ]);

        return $updated ? $newSecret : null;
    }

    /**
     * Get keys by user
     */
    public function getByUser(int $userId, bool $activeOnly = false): array
    {
        $query = $this->where('user_id', $userId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Log API usage
     */
    public function logUsage(string $key, string $endpoint, array $data = []): void
    {
        $activityLog = model('App\Models\CMS\ActivityLogModel');

        $activityLog->log('api_usage', 'request', "API request to {$endpoint}", array_merge([
            'api_key' => $key,
            'endpoint' => $endpoint,
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'timestamp' => time()
        ], $data));
    }

    /**
     * Get usage statistics
     */
    public function getUsageStats(int $id, string $period = 'day'): array
    {
        $apiKey = $this->find($id);

        if (!$apiKey) {
            return [];
        }

        $activityLog = model('App\Models\CMS\ActivityLogModel');

        $dateFormat = match($period) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        return $activityLog->select("DATE_FORMAT(created_at, '{$dateFormat}') as period")
            ->select('COUNT(*) as requests')
            ->where('type', 'api_usage')
            ->where("JSON_EXTRACT(data, '$.api_key') = '{$apiKey->key}'")
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('period')
            ->orderBy('period', 'ASC')
            ->findAll();
    }

    /**
     * Clean expired keys
     */
    public function cleanExpired(): int
    {
        return $this->where('expires_at IS NOT NULL')
            ->where('expires_at <', Time::now())
            ->delete();
    }

    /**
     * Helper methods
     */
    private function generateSecureKey(): string
    {
        return bin2hex(random_bytes(32)); // 64 characters
    }

    private function generateSecureSecret(): string
    {
        return base64_encode(random_bytes(48)); // 64 characters
    }
}
