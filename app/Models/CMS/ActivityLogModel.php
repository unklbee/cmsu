<?php

// Activity Log Model
namespace App\Models\CMS;

use App\Entities\CMS\ActivityLogEntity;

class ActivityLogModel extends BaseModel
{
    protected $table = 'cms_activity_logs';
    protected $primaryKey = 'id';
    protected $returnType = ActivityLogEntity::class;
    protected $allowedFields = [
        'user_id', 'type', 'model', 'model_id',
        'action', 'description', 'data',
        'ip_address', 'user_agent'
    ];

    protected $useTimestamps = false; // Only created_at
    protected $createdField = 'created_at';

    // Disable activity logging to prevent infinite loop
    protected $enableActivityLog = false;

    /**
     * Log activity manually
     */
    public function log(string $type, string $action, string $description, array $data = []): bool
    {
        $logData = [
            'user_id' => user_id() ?? null,
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'data' => json_encode($data),
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return (bool) $this->insert($logData);
    }

    /**
     * Log model activity
     */
    public function logModel(string $model, int $modelId, string $action, array $changes = []): bool
    {
        $logData = [
            'user_id' => user_id() ?? null,
            'type' => 'model_' . $action,
            'model' => $model,
            'model_id' => $modelId,
            'action' => $action,
            'description' => ucfirst($action) . ' ' . class_basename($model) . ' #' . $modelId,
            'data' => json_encode($changes),
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ];

        return (bool) $this->insert($logData);
    }

    /**
     * Get activity timeline
     */
    public function getTimeline(array $filters = []): array
    {
        $query = $this;

        if (isset($filters['user_id'])) {
            $query = $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['type'])) {
            $query = $query->where('type', $filters['type']);
        }

        if (isset($filters['model'])) {
            $query = $query->where('model', $filters['model']);
        }

        if (isset($filters['date_from'])) {
            $query = $query->where('created_at >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query = $query->where('created_at <=', $filters['date_to']);
        }

        $limit = $filters['limit'] ?? 50;
        $offset = $filters['offset'] ?? 0;

        return $query->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get activity by model
     */
    public function getByModel(string $model, int $modelId): array
    {
        return $this->where('model', $model)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get user activity
     */
    public function getUserActivity(int $userId, int $days = 7): array
    {
        return $this->where('user_id', $userId)
            ->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")))
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get activity statistics
     */
    public function getStatistics(string $period = 'day'): array
    {
        $dateFormat = match($period) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        return $this->select("DATE_FORMAT(created_at, '{$dateFormat}') as period")
            ->select('type, COUNT(*) as count')
            ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
            ->groupBy('period, type')
            ->orderBy('period', 'ASC')
            ->findAll();
    }

    /**
     * Clean old logs
     */
    public function cleanOld(int $days = 90): int
    {
        return $this->where('created_at <', date('Y-m-d', strtotime("-{$days} days")))
            ->delete();
    }
}
