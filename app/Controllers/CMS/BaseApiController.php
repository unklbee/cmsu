<?php

/**
 * Base API Controller
 */
namespace App\Controllers\CMS;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

abstract class BaseApiController extends BaseController
{
    use ResponseTrait;

    protected $format = 'json';
    protected $apiKey = null;
    protected $rateLimit = true;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Set CORS headers
        $this->setCorsHeaders();

        // Validate API key if required
        if ($this->requiresAuth()) {
            $this->validateApiKey();
        }

        // Check rate limit
        if ($this->rateLimit && $this->apiKey) {
            $this->checkRateLimit();
        }
    }

    /**
     * Check if endpoint requires authentication
     */
    protected function requiresAuth(): bool
    {
        return true;
    }

    /**
     * Validate API key
     */
    protected function validateApiKey(): void
    {
        $key = $this->getApiKey();
        $secret = $this->getApiSecret();

        if (!$key || !$secret) {
            $this->failUnauthorized('API key required');
        }

        $apiKeyModel = model('App\Models\CMS\ApiKeyModel');
        $this->apiKey = $apiKeyModel->validate($key, $secret);

        if (!$this->apiKey) {
            $this->failUnauthorized('Invalid API key');
        }
    }

    /**
     * Check rate limit
     */
    protected function checkRateLimit(): void
    {
        $apiKeyModel = model('App\Models\CMS\ApiKeyModel');
        $identifier = $this->request->getIPAddress();

        if (!$apiKeyModel->checkRateLimit($this->apiKey->key, $identifier)) {
            $this->failTooManyRequests('Rate limit exceeded');
        }

        // Add rate limit headers
        $remaining = $apiKeyModel->getRemainingLimit($this->apiKey->key, $identifier);
        $this->response->setHeader('X-RateLimit-Limit', $this->apiKey->rate_limit);
        $this->response->setHeader('X-RateLimit-Remaining', $remaining);
    }

    /**
     * Check API permission
     */
    protected function checkPermission(string $permission): void
    {
        if (!$this->apiKey->hasPermission($permission)) {
            $this->failForbidden('Insufficient permissions');
        }
    }

    /**
     * Get API key from request
     */
    protected function getApiKey(): ?string
    {
        // Check header first
        $key = $this->request->getHeaderLine('X-API-Key');

        if (!$key) {
            // Check query parameter
            $key = $this->request->getGet('api_key');
        }

        return $key;
    }

    /**
     * Get API secret from request
     */
    protected function getApiSecret(): ?string
    {
        // Check header first
        $secret = $this->request->getHeaderLine('X-API-Secret');

        if (!$secret) {
            // Check Authorization header
            $auth = $this->request->getHeaderLine('Authorization');
            if (strpos($auth, 'Bearer ') === 0) {
                $secret = substr($auth, 7);
            }
        }

        return $secret;
    }

    /**
     * Set CORS headers
     */
    protected function setCorsHeaders(): void
    {
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $this->response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key, X-API-Secret');

        // Handle preflight
        if ($this->request->getMethod() === 'options') {
            $this->response->setStatusCode(200);
            exit;
        }
    }

    /**
     * Log API usage
     */
    protected function logApiUsage(array $data = []): void
    {
        if ($this->apiKey) {
            $apiKeyModel = model('App\Models\CMS\ApiKeyModel');
            $apiKeyModel->logUsage(
                $this->apiKey->key,
                current_url(),
                $data
            );
        }
    }

    /**
     * Paginate results
     */
    protected function paginate($model, int $perPage = 20): array
    {
        $page = (int) $this->request->getGet('page') ?? 1;
        $data = $model->paginate($perPage, 'default', $page);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $model->pager->getTotal(),
                'total_pages' => $model->pager->getPageCount(),
                'links' => $model->pager->links()
            ]
        ];
    }
}
