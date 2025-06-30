<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

/**
 * Module Management Command
 * File: app/Commands/CMSModule.php
 */
class CMSModule extends BaseCommand
{
    protected $group = 'CMS';
    protected $name = 'cms:module';
    protected $description = 'Manage CMS modules';
    protected $usage = 'cms:module [action] [module]';
    protected $arguments = [
        'action' => 'Action to perform: list, install, uninstall, enable, disable',
        'module' => 'Module name (for install/uninstall/enable/disable)'
    ];

    public function run(array $params): void
    {
        $action = $params[0] ?? 'list';
        $module = $params[1] ?? null;

        switch ($action) {
            case 'list':
                $this->listModules();
                break;

            case 'install':
                if (!$module) {
                    CLI::error('Module name required');
                    return;
                }
                $this->installModule($module);
                break;

            case 'uninstall':
                if (!$module) {
                    CLI::error('Module name required');
                    return;
                }
                $this->uninstallModule($module);
                break;

            case 'enable':
                if (!$module) {
                    CLI::error('Module name required');
                    return;
                }
                $this->toggleModule($module, true);
                break;

            case 'disable':
                if (!$module) {
                    CLI::error('Module name required');
                    return;
                }
                $this->toggleModule($module, false);
                break;

            default:
                CLI::error("Unknown action: $action");
        }
    }

    private function listModules(): void
    {
        $moduleModel = model('App\Models\CMS\ModuleModel');
        $modules = $moduleModel->findAll();

        if (empty($modules)) {
            CLI::write('No modules installed', 'yellow');
            return;
        }

        $tbody = [];
        foreach ($modules as $module) {
            $status = $module->status === 'active'
                ? CLI::color('Active', 'green')
                : CLI::color('Inactive', 'red');

            $tbody[] = [
                $module->name,
                $module->display_name,
                $module->version,
                $status
            ];
        }

        CLI::table($tbody, ['Name', 'Display Name', 'Version', 'Status']);
    }

    private function installModule(string $moduleName): void
    {
        try {
            $moduleModel = model('App\Models\CMS\ModuleModel');

            CLI::write("Installing module: $moduleName...", 'yellow');

            if ($moduleModel->installModule($moduleName)) {
                CLI::write("Module '$moduleName' installed successfully!", 'green');
            } else {
                CLI::error("Failed to install module '$moduleName'");
            }
        } catch (Exception $e) {
            CLI::error($e->getMessage());
        }
    }

    private function uninstallModule(string $moduleName): void
    {
        if (!CLI::prompt("Are you sure you want to uninstall '$moduleName'?", ['y', 'n']) === 'y') {
            return;
        }

        try {
            $moduleModel = model('App\Models\CMS\ModuleModel');

            CLI::write("Uninstalling module: $moduleName...", 'yellow');

            if ($moduleModel->uninstallModule($moduleName)) {
                CLI::write("Module '$moduleName' uninstalled successfully!", 'green');
            } else {
                CLI::error("Failed to uninstall module '$moduleName'");
            }
        } catch (Exception $e) {
            CLI::error($e->getMessage());
        }
    }

    private function toggleModule(string $moduleName, bool $enable): void
    {
        try {
            $moduleModel = model('App\Models\CMS\ModuleModel');
            $module = $moduleModel->where('name', $moduleName)->first();

            if (!$module) {
                CLI::error("Module '$moduleName' not found");
                return;
            }

            $status = $enable ? 'active' : 'inactive';

            if ($moduleModel->update($module->id, ['status' => $status])) {
                $action = $enable ? 'enabled' : 'disabled';
                CLI::write("Module '$moduleName' $action successfully!", 'green');
            } else {
                CLI::error("Failed to update module status");
            }
        } catch (Exception $e) {
            CLI::error($e->getMessage());
        }
    }
}