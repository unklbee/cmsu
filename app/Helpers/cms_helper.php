<?php

/**
 * CMS Helper Functions
 * Location: app/Helpers/cms_helper.php
 */

if (!function_exists('get_avatar')) {
    /**
     * Get user avatar URL
     */
    function get_avatar($user, $size = 32): string
    {
        // If user has avatar in media
        if (isset($user->avatar_id) && $user->avatar_id) {
            return media_url($user->avatar_id, 'small');
        }

        // Use Gravatar as fallback
        $email = $user->email ?? 'default@example.com';
        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }
}

if (!function_exists('cms_setting')) {
    /**
     * Get setting value
     */
    function cms_setting(string $key, $default = null)
    {
        return service('cms')->getSetting($key, $default);
    }
}

if (!function_exists('set_cms_setting')) {
    /**
     * Set setting value
     */
    function set_cms_setting(string $key, $value): bool
    {
        $settingModel = model('App\Models\CMS\SettingModel');
        return $settingModel->set($key, $value);
    }
}

if (!function_exists('module_enabled')) {
    /**
     * Check if module is enabled
     */
    function module_enabled(string $module): bool
    {
        return service('cms')->isModuleActive($module);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check user permission
     */
    function has_permission(string $permission): bool
    {
        if (!auth()->loggedIn()) {
            return false;
        }

        $userId = auth()->id();
        $db = \Config\Database::connect();

        // Check if user is superadmin
        $isSuperadmin = $db->table('auth_groups_users')
                ->where('user_id', $userId)
                ->where('group', 'superadmin')
                ->countAllResults() > 0;

        if ($isSuperadmin) {
            return true; // Superadmin has all permissions
        }

        // Check specific permission
        $hasPermission = $db->table('auth_permissions_users')
                ->where('user_id', $userId)
                ->where('permission', $permission)
                ->countAllResults() > 0;

        return $hasPermission;
    }
}

if (!function_exists('notify_user')) {
    /**
     * Send notification to user
     */
    function notify_user(int $userId, string $type, string $title, string $message, array $options = []): bool
    {
        $notificationModel = model('App\Models\CMS\NotificationModel');
        return $notificationModel->notify($userId, $type, $title, $message, $options);
    }
}

if (!function_exists('log_activity')) {
    /**
     * Log activity
     */
    function log_activity(string $type, string $action, string $description, array $data = []): bool
    {
        $activityLog = model('App\Models\CMS\ActivityLogModel');
        return $activityLog->log($type, $action, $description, $data);
    }
}

if (!function_exists('media_url')) {
    /**
     * Get media URL
     */
    function media_url(int $mediaId, string $size = 'original'): string
    {
        $mediaModel = model('App\Models\CMS\MediaModel');
        $media = $mediaModel->find($mediaId);

        if (!$media) {
            return '';
        }

        if ($size !== 'original' && $media->isImage()) {
            return $media->getThumbnail($size);
        }

        return $media->url;
    }
}

if (!function_exists('render_menu')) {
    /**
     * Render menu HTML
     */
    function render_menu(string $group = 'main', array $options = []): string
    {
        $menus = service('cms')->getMenu($group);

        $class = $options['class'] ?? 'nav';
        $itemClass = $options['item_class'] ?? 'nav-item';
        $linkClass = $options['link_class'] ?? 'nav-link';
        $activeClass = $options['active_class'] ?? 'active';
        $dropdownClass = $options['dropdown_class'] ?? 'dropdown';

        return render_menu_items($menus, $class, $itemClass, $linkClass, $activeClass, $dropdownClass);
    }
}

if (!function_exists('render_menu_items')) {
    /**
     * Recursively render menu items
     */
    function render_menu_items(array $items, string $class, string $itemClass, string $linkClass, string $activeClass, string $dropdownClass, int $level = 0): string
    {
        $html = '<ul class="' . $class . '">';

        foreach ($items as $item) {
            $hasChildren = !empty($item->children);
            $isActive = $item->isActive();

            $liClass = $itemClass;
            if ($hasChildren) {
                $liClass .= ' ' . $dropdownClass;
            }
            if ($isActive) {
                $liClass .= ' ' . $activeClass;
            }

            $html .= '<li class="' . $liClass . '">';

            $aClass = $linkClass;
            if ($isActive) {
                $aClass .= ' ' . $activeClass;
            }
            if ($hasChildren) {
                $aClass .= ' dropdown-toggle';
            }

            $html .= '<a href="' . $item->getUrl() . '" class="' . $aClass . '"';
            if ($item->target === '_blank') {
                $html .= ' target="_blank"';
            }
            if ($hasChildren) {
                $html .= ' data-toggle="dropdown"';
            }
            $html .= '>';

            if ($item->icon) {
                $html .= $item->getIcon() . ' ';
            }

            $html .= $item->title;

            if ($hasChildren) {
                $html .= ' <span class="caret"></span>';
            }

            $html .= '</a>';

            if ($hasChildren) {
                $html .= render_menu_items($item->children, 'dropdown-menu', $itemClass, $linkClass, $activeClass, $dropdownClass, $level + 1);
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }
}

if (!function_exists('breadcrumbs')) {
    /**
     * Render breadcrumbs
     */
    function breadcrumbs(array $items, array $options = []): string
    {
        $class = $options['class'] ?? 'breadcrumb';
        $itemClass = $options['item_class'] ?? 'breadcrumb-item';
        $activeClass = $options['active_class'] ?? 'active';

        $html = '<ol class="' . $class . '">';

        foreach ($items as $index => $item) {
            $isLast = $index === count($items) - 1;

            $html .= '<li class="' . $itemClass;
            if ($isLast) {
                $html .= ' ' . $activeClass;
            }
            $html .= '">';

            if (!$isLast && isset($item['url']) && $item['url']) {
                $html .= '<a href="' . $item['url'] . '">' . $item['title'] . '</a>';
            } else {
                $html .= $item['title'];
            }

            $html .= '</li>';
        }

        $html .= '</ol>';

        return $html;
    }
}

if (!function_exists('format_bytes')) {
    /**
     * Format bytes to human readable
     */
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('time_ago')) {
    /**
     * Get time ago string
     */
    function time_ago($datetime): string
    {
        if (is_string($datetime)) {
            $datetime = new DateTime($datetime);
        }

        $now = new DateTime();
        $interval = $now->diff($datetime);

        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        }
        if ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        }
        if ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        }
        if ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        }
        if ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        }

        return 'just now';
    }
}

if (!function_exists('cms_cache')) {
    /**
     * CMS cache helper with tags
     */
    function cms_cache(string $key, callable $callback = null, int $ttl = 300, array $tags = [])
    {
        $cache = service('cmsCache');

        if ($callback === null) {
            return $cache->get($key, $tags);
        }

        return $cache->remember($key, $ttl, $callback, $tags);
    }
}

if (!function_exists('clear_cms_cache')) {
    /**
     * Clear CMS cache by tag
     */
    function clear_cms_cache(string $tag): bool
    {
        return service('cmsCache')->flushTag($tag);
    }
}

if (!function_exists('run_hook')) {
    /**
     * Run module hooks
     */
    function run_hook(string $hook, array $params = []): array
    {
        return service('cms')->runHook($hook, $params);
    }
}

if (!function_exists('asset_version')) {
    /**
     * Add version to asset URL for cache busting
     */
    function asset_version(string $path): string
    {
        $file = FCPATH . $path;

        if (file_exists($file)) {
            $version = filemtime($file);
            return base_url($path . '?v=' . $version);
        }

        return base_url($path);
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize filename
     */
    function sanitize_filename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9\-\_\.]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Trim underscores
        return trim($filename, '_');
    }
}

if (!function_exists('generate_slug')) {
    /**
     * Generate URL slug
     */
    function generate_slug(string $text): string
    {
        // Convert to lowercase
        $slug = strtolower($text);

        // Replace spaces with hyphens
        $slug = str_replace(' ', '-', $slug);

        // Remove special characters
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

        // Remove multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim hyphens
        return trim($slug, '-');
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncate text
     */
    function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('array_to_options')) {
    /**
     * Convert array to HTML options
     */
    function array_to_options(array $array, $selected = null, bool $placeholder = true): string
    {
        $html = '';

        if ($placeholder) {
            $html .= '<option value="">-- Select --</option>';
        }

        foreach ($array as $value => $label) {
            $html .= '<option value="' . $value . '"';

            if ($selected !== null && $value == $selected) {
                $html .= ' selected';
            }

            $html .= '>' . $label . '</option>';
        }

        return $html;
    }
}

if (!function_exists('flash_message')) {
    /**
     * Get flash message HTML
     */
    function flash_message(): string
    {
        $session = session();
        $html = '';

        $types = ['success', 'error', 'warning', 'info'];

        foreach ($types as $type) {
            if ($session->has($type)) {
                $message = $session->getFlashdata($type);
                $class = $type === 'error' ? 'danger' : $type;

                $html .= '<div class="alert alert-' . $class . ' alert-dismissible fade show" role="alert">';
                $html .= $message;
                $html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
                $html .= '<span aria-hidden="true">&times;</span>';
                $html .= '</button>';
                $html .= '</div>';
            }
        }

        return $html;
    }
}

if (!function_exists('character_limiter')) {
    /**
     * Character limiter
     */
    function character_limiter(string $str, int $n = 500, string $end_char = '&#8230;'): string
    {
        if (mb_strlen($str) < $n) {
            return $str;
        }

        $str = preg_replace('/\s+/', ' ', str_replace(["\r\n", "\r", "\n"], ' ', $str));

        if (mb_strlen($str) <= $n) {
            return $str;
        }

        $out = '';
        foreach (explode(' ', trim($str)) as $val) {
            $out .= $val . ' ';

            if (mb_strlen($out) >= $n) {
                $out = trim($out);
                return (mb_strlen($out) === mb_strlen($str)) ? $out : $out . $end_char;
            }
        }

        return $out;
    }
}