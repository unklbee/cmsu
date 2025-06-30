<?php

/**
 * Admin Dashboard Controller
 * File: app/Controllers/Admin/Dashboard.php
 */
namespace App\Controllers\Admin;

use App\Controllers\CMS\BaseAdminController;

class Dashboard extends BaseAdminController
{
    public function index()
    {
        $this->setTitle('Dashboard');

        // Get statistics
        $data = [
            'stats' => $this->getStatistics(),
            'recent_activities' => $this->getRecentActivities(),
            'system_info' => $this->getSystemInfo()
        ];

        return $this->render('dashboard', $data);
    }

    private function getStatistics(): array
    {
        $db = \Config\Database::connect();

        return [
            'total_users' => $db->table('users')->countAll(),
            'active_users' => $db->table('users')->where('active', 1)->countAllResults(),
            'total_media' => $db->table('cms_media')->countAll(),
            'total_modules' => $db->table('cms_modules')->where('status', 'active')->countAllResults()
        ];
    }

    private function getRecentActivities(): array
    {
        $activityLog = model('App\Models\CMS\ActivityLogModel');
        return $activityLog->getTimeline(['limit' => 10]);
    }

    private function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'ci_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'cms_version' => '1.0.0',
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
//            'database' => $this->db->getPlatform(),
            'environment' => ENVIRONMENT
        ];
    }
}
