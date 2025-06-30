<?php

/**
 * Theme Service - Theme management
 */

namespace App\Services\CMS;

class ThemeService
{
    protected $activeTheme;
    protected $themePath;
    protected $themes = [];

    public function __construct()
    {
        $cms = service('cms');
        $this->activeTheme = $cms->getSetting('theme', 'default');
        $this->themePath = FCPATH . 'themes/';
        $this->loadThemes();
    }

    /**
     * Load available themes
     */
    protected function loadThemes(): void
    {
        if (!is_dir($this->themePath)) {
            return;
        }

        $dirs = scandir($this->themePath);

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($this->themePath . $dir)) {
                continue;
            }

            $configFile = $this->themePath . $dir . '/theme.json';

            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                $this->themes[$dir] = $config;
            }
        }
    }

    /**
     * Get all themes
     */
    public function getThemes(): array
    {
        return $this->themes;
    }

    /**
     * Get active theme
     */
    public function getActiveTheme(): string
    {
        return $this->activeTheme;
    }

    /**
     * Get theme info
     */
    public function getThemeInfo(string $theme = null): ?array
    {
        $theme = $theme ?? $this->activeTheme;
        return $this->themes[$theme] ?? null;
    }

    /**
     * Render theme view
     * @throws \Exception
     */
    public function render(string $view, array $data = []): string
    {
        // First try to find view in theme directory
        $themeViewPath = 'themes/' . $this->activeTheme . '/' . $view;

        if ($this->viewExists($themeViewPath)) {
            return view($themeViewPath, $data);
        }

        // Fallback to default theme
        $defaultViewPath = 'themes/default/' . $view;
        if ($this->viewExists($defaultViewPath)) {
            return view($defaultViewPath, $data);
        }

        // Finally, try the main Views directory
        if ($this->viewExists($view)) {
            return view($view, $data);
        }

        throw new \Exception("View not found: {$view}");
    }

    /**
     * Check if view exists
     */
    protected function viewExists(string $view): bool
    {
        // Check in theme directory (public/themes/)
        $themeViewPath = FCPATH . $view . '.php';
        if (file_exists($themeViewPath)) {
            return true;
        }

        // Check in main Views directory (app/Views/)
        $mainViewPath = APPPATH . 'Views/' . $view . '.php';
        if (file_exists($mainViewPath)) {
            return true;
        }

        return false;
    }

    /**
     * Get theme asset URL
     */
    public function asset(string $path): string
    {
        return base_url('themes/' . $this->activeTheme . '/assets/' . $path);
    }

    /**
     * Add theme CSS
     */
    public function css(string $file): string
    {
        $url = $this->asset('css/' . $file);
        return '<link rel="stylesheet" href="' . $url . '">';
    }

    /**
     * Add theme JS
     */
    public function js(string $file): string
    {
        $url = $this->asset('js/' . $file);
        return '<script src="' . $url . '"></script>';
    }

    /**
     * Get theme configuration
     */
    public function config(string $key = null, $default = null)
    {
        $info = $this->getThemeInfo();

        if (!$info) {
            return $default;
        }

        if ($key === null) {
            return $info;
        }

        return dot_array_search($key, $info) ?? $default;
    }
}
