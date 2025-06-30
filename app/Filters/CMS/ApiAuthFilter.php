<?php

namespace App\Filters\CMS;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * API Authentication Filter
 */
class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $apiKeyModel = model('App\Models\CMS\ApiKeyModel');

        // Get API credentials
        $key = $this->getApiKey($request);
        $secret = $this->getApiSecret($request);

        if (!$key || !$secret) {
            return $this->unauthorizedResponse('API key required');
        }

        // Validate API key
        $apiKey = $apiKeyModel->validate($key, $secret);

        if (!$apiKey) {
            return $this->unauthorizedResponse('Invalid API key');
        }

        // Store API key for use in controller
        $request->apiKey = $apiKey;

        // Check permissions if provided
        if ($arguments) {
            foreach ($arguments as $permission) {
                if (!$apiKey->hasPermission($permission)) {
                    return $this->forbiddenResponse('Insufficient permissions');
                }
            }
        }

        // Check rate limit
        $identifier = $request->getIPAddress();
        if (!$apiKeyModel->checkRateLimit($key, $identifier)) {
            return $this->tooManyRequestsResponse();
        }

        // Log API usage
        $apiKeyModel->logUsage($key, current_url());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add rate limit headers
        if (isset($request->apiKey)) {
            $apiKeyModel = model('App\Models\CMS\ApiKeyModel');
            $remaining = $apiKeyModel->getRemainingLimit(
                $request->apiKey->key,
                $request->getIPAddress()
            );

            $response->setHeader('X-RateLimit-Limit', $request->apiKey->rate_limit);
            $response->setHeader('X-RateLimit-Remaining', $remaining);
            $response->setHeader('X-RateLimit-Reset', time() + 60);
        }
    }

    private function getApiKey(RequestInterface $request): ?string
    {
        // Check header first
        $key = $request->getHeaderLine('X-API-Key');

        if (!$key) {
            // Check query parameter
            $key = $request->getGet('api_key');
        }

        return $key;
    }

    private function getApiSecret(RequestInterface $request): ?string
    {
        // Check header first
        $secret = $request->getHeaderLine('X-API-Secret');

        if (!$secret) {
            // Check Authorization header
            $auth = $request->getHeaderLine('Authorization');
            if (strpos($auth, 'Bearer ') === 0) {
                $secret = substr($auth, 7);
            }
        }

        return $secret;
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        return response()->setStatusCode(401)->setJSON([
            'success' => false,
            'message' => $message
        ]);
    }

    private function forbiddenResponse(string $message): ResponseInterface
    {
        return response()->setStatusCode(403)->setJSON([
            'success' => false,
            'message' => $message
        ]);
    }

    private function tooManyRequestsResponse(): ResponseInterface
    {
        return response()->setStatusCode(429)->setJSON([
            'success' => false,
            'message' => 'Rate limit exceeded'
        ]);
    }
}
