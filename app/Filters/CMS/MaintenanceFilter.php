<?php
namespace App\Filters\CMS;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Maintenance Mode Filter
 */
class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $cms = service('cms');

        // Check if maintenance mode is enabled
        if ($cms->getSetting('maintenance_mode', false)) {
            // Allow admin access
            if (auth()->loggedIn() && auth()->user()->inGroup('admin', 'superadmin')) {
                return;
            }

            // Allow specific IPs
            $allowedIps = $cms->getSetting('maintenance_allowed_ips', []);
            if (in_array($request->getIPAddress(), $allowedIps)) {
                return;
            }

            // Show maintenance page
            $message = $cms->getSetting('maintenance_message', 'Site is under maintenance');

            if ($request->isAJAX() || strpos($request->getPath(), 'api/') === 0) {
                return response()->setStatusCode(503)->setJSON([
                    'success' => false,
                    'message' => $message
                ]);
            }

            return response()->setStatusCode(503)->setBody(view('errors/maintenance', [
                'message' => $message
            ]));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
