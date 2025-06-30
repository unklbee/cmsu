<?php

namespace App\Filters\CMS;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Authentication Filter
 */
class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            // Save intended URL
            session()->set('redirect_url', current_url());

            return redirect()->to('/login')->with('error', 'Please login to continue');
        }

        // Check if user has admin access
        if (!auth()->user()->inGroup('admin', 'superadmin')) {
            return redirect()->to('/')->with('error', 'Access denied');
        }

        // Check specific permissions if provided
        if ($arguments) {
            $hasPermission = false;

            foreach ($arguments as $permission) {
                if (auth()->user()->can($permission)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                if ($request->isAJAX()) {
                    return response()->setStatusCode(403)->setJSON([
                        'success' => false,
                        'message' => 'Insufficient permissions'
                    ]);
                }

                throw new PageNotFoundException();
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
