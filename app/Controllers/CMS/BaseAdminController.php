<?php

namespace App\Controllers\CMS;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Base Admin Controller
 */
abstract class BaseAdminController extends BaseController
{
    protected $helpers = ['auth', 'setting', 'cms'];
    protected $data = [];
    protected $viewPath = 'admin/';
    protected $theme;
    protected $cms;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Initialize services
        $this->cms = service('cms');
        $this->theme = service('theme');

        // Check authentication
        if (!auth()->loggedIn()) {
            redirect()->to('/login')->send();
            exit;
        }

        // Set default data
        $this->data = [
            'user' => auth()->user(),
            'title' => 'Dashboard',
            'breadcrumbs' => [],
            'notifications' => $this->getNotifications(),
            'menus' => [
                'admin' => $this->cms->getMenu('admin'),
                'user' => $this->cms->getMenu('user')
            ]
        ];

        // Log activity
        $this->logActivity();
    }

    /**
     * Render view with theme
     */
    protected function render(string $view, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);

        // Set breadcrumbs
        if (empty($this->data['breadcrumbs'])) {
            $this->data['breadcrumbs'] = $this->generateBreadcrumbs();
        }

        return $this->theme->render($this->viewPath . $view, $this->data);
    }

    /**
     * Add breadcrumb
     */
    protected function addBreadcrumb(string $title, string $url = null): self
    {
        $this->data['breadcrumbs'][] = [
            'title' => $title,
            'url' => $url
        ];

        return $this;
    }

    /**
     * Set page title
     */
    protected function setTitle(string $title): self
    {
        $this->data['title'] = $title;
        return $this;
    }

    /**
     * Check permission
     */
    protected function checkPermission(string $permission): void
    {
        if (!auth()->user()->can($permission)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    /**
     * JSON response helper
     */
    protected function jsonResponse(array $data, int $status = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($status)
            ->setJSON($data);
    }

    /**
     * Success response
     */
    protected function success($data = null, string $message = 'Success', int $status = 200): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response
     */
    protected function error(string $message = 'Error', $errors = null, int $status = 400): ResponseInterface
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Get user notifications
     */
    private function getNotifications(): array
    {
        $notificationModel = model('App\Models\CMS\NotificationModel');
        return $notificationModel->getUnread(auth()->id(), 5);
    }

    /**
     * Log user activity
     */
    private function logActivity(): void
    {
        $activityLog = model('App\Models\CMS\ActivityLogModel');
        $activityLog->log(
            'page_view',
            'view',
            'Viewed ' . current_url()
        );
    }

    /**
     * Generate breadcrumbs from URL
     */
    private function generateBreadcrumbs(): array
    {
        $breadcrumbs = [
            ['title' => 'Dashboard', 'url' => site_url('admin')]
        ];

        $uri = $this->request->getUri();
        $segments = $uri->getSegments();
        array_shift($segments); // Remove 'admin'

        $url = 'admin';
        foreach ($segments as $segment) {
            $url .= '/' . $segment;
            $breadcrumbs[] = [
                'title' => ucfirst(str_replace('-', ' ', $segment)),
                'url' => site_url($url)
            ];
        }

        // Remove URL from last item
        if (count($breadcrumbs) > 1) {
            $breadcrumbs[count($breadcrumbs) - 1]['url'] = null;
        }

        return $breadcrumbs;
    }
}
