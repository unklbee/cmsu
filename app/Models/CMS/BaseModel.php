<?php

namespace App\Models\CMS;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

abstract class BaseModel extends Model
{
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;

    // Auto logging
    protected $enableActivityLog = true;
    protected $logIgnoredFields = ['updated_at', 'created_at'];

    // Caching
    protected $enableCache = true;
    protected $cachePrefix = 'cms_';
    protected $cacheDuration = 300; // 5 minutes

    // Query scopes
    protected $globalScopes = [];

    // Events
    protected $beforeInsert = ['setCreatedAt', 'beforeInsertCallback'];
    protected $afterInsert = ['afterInsertCallback', 'logActivity', 'clearCache'];
    protected $beforeUpdate = ['setUpdatedAt', 'beforeUpdateCallback'];
    protected $afterUpdate = ['afterUpdateCallback', 'logActivity', 'clearCache'];
    protected $afterDelete = ['afterDeleteCallback', 'logActivity', 'clearCache'];

    /**
     * Get with caching
     */
    public function findCached($id)
    {
        if (!$this->enableCache) {
            return $this->find($id);
        }

        $cacheKey = $this->getCacheKey('find', $id);
        $cache = cache($cacheKey);

        if ($cache !== null) {
            return $cache;
        }

        $result = $this->find($id);
        cache()->save($cacheKey, $result, $this->cacheDuration);

        return $result;
    }

    /**
     * Get all with caching
     */
    public function findAllCached(array $params = [])
    {
        if (!$this->enableCache) {
            return $this->findAll();
        }

        $cacheKey = $this->getCacheKey('findAll', md5(json_encode($params)));
        $cache = cache($cacheKey);

        if ($cache !== null) {
            return $cache;
        }

        // Apply parameters
        if (isset($params['where'])) {
            $this->where($params['where']);
        }
        if (isset($params['orderBy'])) {
            $this->orderBy($params['orderBy'][0], $params['orderBy'][1] ?? 'ASC');
        }
        if (isset($params['limit'])) {
            $this->limit($params['limit'], $params['offset'] ?? 0);
        }

        $result = $this->findAll();
        cache()->save($cacheKey, $result, $this->cacheDuration);

        return $result;
    }

    /**
     * Advanced search with multiple conditions
     */
    public function search(array $conditions, array $options = [])
    {
        $builder = $this->builder();

        // Apply conditions
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $operator = $value['operator'] ?? '=';
                $val = $value['value'];

                switch ($operator) {
                    case 'like':
                        $builder->like($field, $val);
                        break;
                    case 'in':
                        $builder->whereIn($field, $val);
                        break;
                    case 'between':
                        $builder->where("$field >=", $val[0]);
                        $builder->where("$field <=", $val[1]);
                        break;
                    default:
                        $builder->where($field . ' ' . $operator, $val);
                }
            } else {
                $builder->where($field, $value);
            }
        }

        // Apply options
        if (isset($options['orderBy'])) {
            foreach ($options['orderBy'] as $order) {
                $builder->orderBy($order[0], $order[1] ?? 'ASC');
            }
        }

        if (isset($options['groupBy'])) {
            $builder->groupBy($options['groupBy']);
        }

        if (isset($options['having'])) {
            $builder->having($options['having']);
        }

        // Pagination
        if (isset($options['page']) && isset($options['perPage'])) {
            $page = (int) $options['page'];
            $perPage = (int) $options['perPage'];
            $offset = ($page - 1) * $perPage;

            $total = $builder->countAllResults(false);
            $results = $builder->limit($perPage, $offset)->get()->getResultArray();

            return [
                'data' => $results,
                'pagination' => [
                    'page' => $page,
                    'perPage' => $perPage,
                    'total' => $total,
                    'totalPages' => ceil($total / $perPage)
                ]
            ];
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Bulk operations
     */
//    public function updateBatch(array $data, array $constraints = null)
//    {
//        $result = parent::updateBatch($data, $constraints);
//
//        if ($result) {
//            $this->clearCache();
//            $this->logBulkActivity('update', count($data));
//        }
//
//        return $result;
//    }
    public function updateBatch(?array $set = null, ?string $index = null, int $batchSize = 100, bool $returnSQL = false)
    {
        $result = parent::updateBatch($set, $index, $batchSize, $returnSQL);

        if ($result) {
            $this->clearCache();
            $this->logBulkActivity('update', is_array($set) ? count($set) : 0);
        }

        return $result;
    }


    public function deleteBatch(array $ids)
    {
        $result = $this->whereIn($this->primaryKey, $ids)->delete();

        if ($result) {
            $this->clearCache();
            $this->logBulkActivity('delete', count($ids));
        }

        return $result;
    }

    /**
     * Relationships
     */
    public function belongsTo(string $model, string $foreignKey = null, string $ownerKey = 'id')
    {
        $foreignKey = $foreignKey ?? strtolower(basename(str_replace('\\', '/', $model))) . '_id';
        $relatedModel = new $model();

        return function($id) use ($relatedModel, $foreignKey, $ownerKey) {
            return $relatedModel->where($ownerKey, $this->find($id)[$foreignKey] ?? null)->first();
        };
    }

    public function hasMany(string $model, string $foreignKey = null, string $localKey = null)
    {
        $localKey = $localKey ?? $this->primaryKey;
        $foreignKey = $foreignKey ?? strtolower(basename(str_replace('\\', '/', get_class($this)))) . '_id';
        $relatedModel = new $model();

        return function($id) use ($relatedModel, $foreignKey, $localKey) {
            return $relatedModel->where($foreignKey, $this->find($id)[$localKey] ?? null)->findAll();
        };
    }

    /**
     * Activity logging
     */
    protected function logActivity(array $data)
    {
        if (!$this->enableActivityLog) {
            return true;
        }

        // Jangan log aktivitas jika sedang dijalankan dari CLI (contoh: seeder/migrasi)
        if (is_cli()) {
            return true;
        }

        $activityLog = model('App\Models\CMS\ActivityLogModel');
        $eventType = $this->getEventType();

        if (!$eventType) {
            return true;
        }

        $oldData = null;
        $newData = null;

        if ($eventType === 'update' && isset($data['id'])) {
            $oldData = $this->find($data['id'][0]);
            $newData = $this->getChangedData($oldData, $data['data'][0]);
        }

        $logData = [
            'user_id' => user_id() ?? null,
            'type' => $eventType,
            'model' => get_class($this),
            'model_id' => $data['id'][0] ?? ($data['result']->insertID ?? null),
            'action' => $eventType,
            'description' => $this->getActivityDescription($eventType, $data),
            'data' => json_encode([
                'old' => $oldData,
                'new' => $newData ?? ($data['data'][0] ?? null)
            ]),
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'created_at' => Time::now()
        ];

        $activityLog->skipValidation(true)->insert($logData);

        return true;
    }

    protected function logBulkActivity(string $action, int $count)
    {
        if (!$this->enableActivityLog) {
            return;
        }

        $activityLog = model('App\Models\CMS\ActivityLogModel');

        $logData = [
            'user_id' => user_id() ?? null,
            'type' => 'bulk_' . $action,
            'model' => get_class($this),
            'action' => 'bulk_' . $action,
            'description' => "Bulk {$action} {$count} records",
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'created_at' => Time::now()
        ];

        $activityLog->skipValidation(true)->insert($logData);
    }

    /**
     * Cache management
     */
    protected function getCacheKey(string $method, $identifier = null)
    {
        $key = $this->cachePrefix . $this->table . '_' . $method;

        if ($identifier !== null) {
            $key .= '_' . $identifier;
        }

        return $key;
    }

    public function clearCache()
    {
        if (!$this->enableCache) {
            return true;
        }

        // Clear all cache for this model
        cache()->deleteMatching($this->cachePrefix . $this->table . '_*');

        return true;
    }

    /**
     * Helper methods
     */
    protected function getEventType()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($trace as $item) {
            if (isset($item['function']) && in_array($item['function'], ['insert', 'update', 'delete'])) {
                return $item['function'];
            }
        }

        return null;
    }

    protected function getChangedData(array $oldData, array $newData)
    {
        $changed = [];

        foreach ($newData as $key => $value) {
            if (!in_array($key, $this->logIgnoredFields) &&
                (!isset($oldData[$key]) || $oldData[$key] !== $value)) {
                $changed[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $value
                ];
            }
        }

        return $changed;
    }

    protected function getActivityDescription(string $type, array $data)
    {
        $table = $this->table;
        $id = $data['id'][0] ?? ($data['result']->insertID ?? 'unknown');

        return ucfirst($type) . " record in {$table} (ID: {$id})";
    }

    /**
     * Callbacks
     */
    protected function setCreatedAt(array $data)
    {
        if (!isset($data['data']['created_at'])) {
            $data['data']['created_at'] = Time::now();
        }

        return $data;
    }

    protected function setUpdatedAt(array $data)
    {
        $data['data']['updated_at'] = Time::now();

        return $data;
    }

    // Override these in child models
    protected function beforeInsertCallback(array $data) { return $data; }
    protected function afterInsertCallback(array $data) { return $data; }
    protected function beforeUpdateCallback(array $data) { return $data; }
    protected function afterUpdateCallback(array $data) { return $data; }
    protected function afterDeleteCallback(array $data) { return $data; }

    /**
     * Query scopes
     */
    public function active()
    {
        return $this->where('is_active', 1);
    }

    public function recent(int $days = 7)
    {
        return $this->where('created_at >=', Time::now()->subDays($days));
    }

    public function byUser($userId = null)
    {
        $userId = $userId ?? user_id();
        return $this->where('user_id', $userId);
    }

    /**
     * JSON field helpers
     */
    public function whereJson(string $column, string $path, $value, string $operator = '=')
    {
        $jsonPath = "JSON_EXTRACT({$column}, '$.{$path}')";

        return $this->where("{$jsonPath} {$operator}", $value);
    }

    public function whereJsonContains(string $column, string $path, $value)
    {
        $jsonPath = "JSON_CONTAINS({$column}, '\"" . $value . "\"', '$.{$path}')";

        return $this->where($jsonPath, 1);
    }
}