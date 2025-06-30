<?php
namespace App\Filters\CMS;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Throttle Filter - Rate limiting for forms
 */
class ThrottleFilter implements FilterInterface
{
    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

    public function before(RequestInterface $request, $arguments = null)
    {
        if ($arguments) {
            $this->maxAttempts = $arguments[0] ?? $this->maxAttempts;
            $this->decayMinutes = $arguments[1] ?? $this->decayMinutes;
        }

        $key = $this->resolveRequestSignature($request);
        $cache = service('cache');

        $attempts = $cache->get($key) ?? 0;

        if ($attempts >= $this->maxAttempts) {
            $retryAfter = $this->decayMinutes * 60;

            if ($request->isAJAX()) {
                return response()->setStatusCode(429)->setJSON([
                    'success' => false,
                    'message' => 'Too many attempts. Please try again later.',
                    'retry_after' => $retryAfter
                ]);
            }

            return redirect()->back()->with('error', 'Too many attempts. Please try again later.');
        }

        $cache->save($key, $attempts + 1, $this->decayMinutes * 60);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }

    protected function resolveRequestSignature(RequestInterface $request): string
    {
        $route = $request->getPath();
        $ip = $request->getIPAddress();
        $user = auth()->id() ?? 'guest';

        return 'throttle_' . md5($route . '|' . $ip . '|' . $user);
    }
}
