<?php
/**
 * API Base Controller
 * File: app/Controllers/Api/V1/BaseApiController.php
 */
namespace App\Controllers\Api\V1;

use App\Controllers\CMS\BaseApiController as CMSBaseApiController;

class BaseApiController extends CMSBaseApiController
{
    protected $format = 'json';

    /**
     * Get pagination data from request
     */
    protected function getPaginationParams(): array
    {
        return [
            'page' => (int) $this->request->getGet('page') ?? 1,
            'per_page' => (int) $this->request->getGet('per_page') ?? 20,
            'sort_by' => $this->request->getGet('sort_by') ?? 'id',
            'sort_order' => $this->request->getGet('sort_order') ?? 'desc'
        ];
    }

    /**
     * Format paginated response
     */
    protected function paginatedResponse($model, array $conditions = []): \CodeIgniter\HTTP\ResponseInterface
    {
        $params = $this->getPaginationParams();

        // Apply conditions
        foreach ($conditions as $key => $value) {
            $model->where($key, $value);
        }

        // Apply sorting
        $model->orderBy($params['sort_by'], $params['sort_order']);

        // Get paginated data
        $data = $model->paginate($params['per_page'], 'default', $params['page']);
        $pager = $model->pager;

        return $this->respond([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $params['page'],
                'per_page' => $params['per_page'],
                'total' => $pager->getTotal(),
                'total_pages' => $pager->getPageCount(),
                'has_more' => $pager->hasMore()
            ]
        ]);
    }
}