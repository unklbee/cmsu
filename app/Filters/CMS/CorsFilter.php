<?php
namespace App\Filters\CMS;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;


/**
 * CORS Filter for API
 */
class CorsFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $response = service('response');

        // Configure allowed origins
        $allowedOrigins = config('Cors')->allowedOrigins ?? ['*'];
        $origin = $request->getHeaderLine('Origin');

        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin ?: '*');
        }

        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-API-Key, X-API-Secret');
        $response->setHeader('Access-Control-Max-Age', '86400');

        // Handle preflight
        if ($request->getMethod() === 'options') {
            return $response->setStatusCode(200);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
