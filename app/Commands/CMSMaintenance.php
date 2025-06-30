<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;


/**
 * Maintenance Mode Command
 * File: app/Commands/CMSMaintenance.php
 */
class CMSMaintenance extends BaseCommand
{
    protected $group = 'CMS';
    protected $name = 'cms:maintenance';
    protected $description = 'Toggle maintenance mode';
    protected $usage = 'cms:maintenance [up|down]';
    protected $arguments = [
        'action' => 'Action: up (disable maintenance) or down (enable maintenance)'
    ];
    protected $options = [
        '--message' => 'Custom maintenance message',
        '--allow' => 'IP addresses to allow (comma separated)'
    ];

    public function run(array $params)
    {
        $action = $params[0] ?? 'status';
        $settings = model('App\Models\CMS\SettingModel');

        switch ($action) {
            case 'down':
                $message = CLI::getOption('message') ?? 'Site is under maintenance. Please check back later.';
                $allow = CLI::getOption('allow');
                $allowedIps = $allow ? explode(',', $allow) : [];

                $settings->setSetting('maintenance_mode', true);
                $settings->setSetting('maintenance_message', $message);
                $settings->setSetting('maintenance_allowed_ips', $allowedIps);

                CLI::write('Maintenance mode enabled', 'yellow');
                CLI::write('Message: ' . $message, 'yellow');

                if (!empty($allowedIps)) {
                    CLI::write('Allowed IPs: ' . implode(', ', $allowedIps), 'yellow');
                }
                break;

            case 'up':
                $settings->setSetting('maintenance_mode', false);
                CLI::write('Maintenance mode disabled', 'green');
                break;

            case 'status':
                $isEnabled = $settings->get('maintenance_mode', false);

                if ($isEnabled) {
                    CLI::write('Maintenance mode is ENABLED', 'yellow');
                    CLI::write('Message: ' . $settings->get('maintenance_message'), 'yellow');

                    $allowedIps = $settings->get('maintenance_allowed_ips', []);
                    if (!empty($allowedIps)) {
                        CLI::write('Allowed IPs: ' . implode(', ', $allowedIps), 'yellow');
                    }
                } else {
                    CLI::write('Maintenance mode is DISABLED', 'green');
                }
                break;

            default:
                CLI::error("Unknown action: {$action}");
        }
    }
}