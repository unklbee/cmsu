<?php
namespace App\Filters\CMS;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Activity Log Filter
 */
class ActivityLogFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do nothing
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Only log successful responses
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            $activityLog = model('App\Models\CMS\ActivityLogModel');

            $type = 'request';
            if ($arguments) {
                $type = $arguments[0] ?? $type;
            }

            $activityLog->log(
                $type,
                $request->getMethod(),
                $request->getPath(),
                [
                    'method' => $request->getMethod(),
                    'path' => $request->getPath(),
                    'query' => $request->getGet(),
                    'status' => $response->getStatusCode()
                ]
            );
        }
    }
}