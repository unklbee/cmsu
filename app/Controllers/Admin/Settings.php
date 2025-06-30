<?php

/**
 * Settings Controller
 * File: app/Controllers/Admin/Settings.php
 */
namespace App\Controllers\Admin;

use App\Controllers\CMS\BaseAdminController;

class Settings extends BaseAdminController
{
    protected $settingModel;

    public function __construct()
    {
        $this->settingModel = model('App\Models\CMS\SettingModel');
    }

    public function index()
    {
        $this->checkPermission('admin.settings');
        $this->setTitle('Settings');

        $data = [
            'settings' => $this->settingModel->getAllSettings()
        ];

        return $this->render('settings/index', $data);
    }

    public function general()
    {
        $this->checkPermission('admin.settings');
        $this->setTitle('General Settings');

        if ($this->request->getMethod() === 'post') {
            return $this->updateSettings();
        }

        $data = [
            'settings' => $this->settingModel->getByGroup('general')
        ];

        return $this->render('settings/general', $data);
    }

    public function email()
    {
        $this->checkPermission('admin.settings');
        $this->setTitle('Email Settings');

        if ($this->request->getMethod() === 'post') {
            return $this->updateSettings('email');
        }

        $data = [
            'settings' => $this->settingModel->getByGroup('email')
        ];

        return $this->render('settings/email', $data);
    }

    public function media()
    {
        $this->checkPermission('admin.settings');
        $this->setTitle('Media Settings');

        if ($this->request->getMethod() === 'post') {
            return $this->updateSettings('media');
        }

        $data = [
            'settings' => $this->settingModel->getByGroup('media')
        ];

        return $this->render('settings/media', $data);
    }

    public function seo()
    {
        $this->checkPermission('admin.settings');
        $this->setTitle('SEO Settings');

        if ($this->request->getMethod() === 'post') {
            return $this->updateSettings('seo');
        }

        $data = [
            'settings' => $this->settingModel->getByGroup('seo')
        ];

        return $this->render('settings/seo', $data);
    }

    public function social()
    {
        $this->checkPermission('admin.settings');
        $this->setTitle('Social Media Settings');

        if ($this->request->getMethod() === 'post') {
            return $this->updateSettings('social');
        }

        $data = [
            'settings' => $this->settingModel->getByGroup('social')
        ];

        return $this->render('settings/social', $data);
    }

    private function updateSettings($group = null)
    {
        $settings = $this->request->getPost();

        // Handle special cases like password fields
        if (isset($settings['smtp_pass']) && empty($settings['smtp_pass'])) {
            unset($settings['smtp_pass']);
        }

        foreach ($settings as $key => $value) {
            $this->settingModel->set($key, $value, ['group' => $group]);
        }

        // Clear cache
        service('cmsCache')->flushTag('settings');

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
