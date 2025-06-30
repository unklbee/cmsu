<?php

namespace App\Controllers\Admin;

use App\Controllers\CMS\BaseAdminController;
use CodeIgniter\HTTP\RedirectResponse;

class Modules extends BaseAdminController
{
    protected $moduleModel;

    public function __construct()
    {
        $this->moduleModel = model('App\Models\CMS\ModuleModel');
    }

    public function index(): string
    {
        $this->checkPermission('admin.modules');
        $this->setTitle('Modules');

        $data = [
            'modules' => $this->moduleModel->findAll(),
            'available_modules' => $this->scanAvailableModules()
        ];

        return $this->render('modules/index', $data);
    }

    public function install($moduleName): RedirectResponse
    {
        $this->checkPermission('admin.modules');

        try {
            $this->moduleModel->installModule($moduleName);
            return redirect()->to('admin/modules')->with('success', 'Module installed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function uninstall($id): RedirectResponse
    {
        $this->checkPermission('admin.modules');

        $module = $this->moduleModel->find($id);
        if (!$module) {
            return redirect()->back()->with('error', 'Module not found');
        }

        try {
            $this->moduleModel->uninstallModule($module->name);
            return redirect()->to('admin/modules')->with('success', 'Module uninstalled successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function config($id)
    {
        $this->checkPermission('admin.modules');

        $module = $this->moduleModel->find($id);
        if (!$module) {
            return redirect()->to('/admin/modules')->with('error', 'Module not found');
        }

        $this->setTitle('Configure ' . $module->display_name);

        if ($this->request->getMethod() === 'post') {
            $config = $this->request->getPost('config');

            try {
                $this->moduleModel->updateConfig($module->name, $config);
                return redirect()->to('/admin/modules/config/' . $id)
                    ->with('success', 'Configuration updated successfully');
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $e->getMessage());
            }
        }

        $data = [
            'module' => $module,
            'config' => $module->config ?? []
        ];

        return $this->render('modules/config', $data);
    }

    public function toggle($id): RedirectResponse
    {
        $this->checkPermission('admin.modules');

        $module = $this->moduleModel->find($id);
        if (!$module) {
            return redirect()->back()->with('error', 'Module not found');
        }

        try {
            $this->moduleModel->toggleModule($module->name);
            return redirect()->back()->with('success', 'Module status updated');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function scanAvailableModules(): array
    {
        $modulesPath = APPPATH . 'Modules/';
        $available = [];

        if (is_dir($modulesPath)) {
            $dirs = scandir($modulesPath);
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..' && is_dir($modulesPath . $dir)) {
                    $configFile = $modulesPath . $dir . '/Config/Module.php';
                    if (file_exists($configFile)) {
                        $config = require $configFile;
                        $available[$dir] = $config;
                    }
                }
            }
        }

        return $available;
    }
}