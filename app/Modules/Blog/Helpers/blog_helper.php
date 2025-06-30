<?php

/**
 * Blog Helper Functions
 * File: app/Modules/Blog/Helpers/blog_helper.php
 */

if (!function_exists('get_recent_posts')) {
    /**
     * Get recent posts
     */
    function get_recent_posts(int $limit = 5): array
    {
        $postModel = model('App\Modules\Blog\Models\PostModel');
        return $postModel->getPublished($limit);
    }
}

if (!function_exists('get_post_categories')) {
    /**
     * Get all post categories
     */
    function get_post_categories(): array
    {
        $categoryModel = model('App\Modules\Blog\Models\CategoryModel');
        return $categoryModel->orderBy('name', 'ASC')->findAll();
    }
}

if (!function_exists('get_popular_tags')) {
    /**
     * Get popular tags
     */
    function get_popular_tags(int $limit = 20): array
    {
        $tagModel = model('App\Modules\Blog\Models\TagModel');
        return $tagModel->getPopular($limit);
    }
}

if (!function_exists('get_recent_comments')) {
    /**
     * Get recent comments
     */
    function get_recent_comments(int $limit = 10): array
    {
        $commentModel = model('App\Modules\Blog\Models\CommentModel');
        return $commentModel->getRecent($limit);
    }
}

if (!function_exists('get_archive_months')) {
    /**
     * Get archive months
     */
    function get_archive_months(int $limit = 12): array
    {
        $db = \Config\Database::connect();

        return $db->table('blog_posts')
            ->select('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
            ->where('status', 'published')
            ->where('published_at <=', date('Y-m-d H:i:s'))
            ->groupBy('year, month')
            ->orderBy('year DESC, month DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}

if (!function_exists('generate_slug')) {
    /**
     * Generate unique slug
     */
    function generate_slug(string $title, string $table = 'blog_posts', int $id = null): string
    {
        helper('text');
        $slug = url_title($title, '-', true);
        $db = \Config\Database::connect();

        // Check if slug exists
        $builder = $db->table($table)->where('slug', $slug);
        if ($id) {
            $builder->where('id !=', $id);
        }

        if ($builder->countAllResults() > 0) {
            // Add number suffix
            $i = 1;
            while (true) {
                $newSlug = $slug . '-' . $i;
                $builder = $db->table($table)->where('slug', $newSlug);
                if ($id) {
                    $builder->where('id !=', $id);
                }

                if ($builder->countAllResults() == 0) {
                    return $newSlug;
                }
                $i++;
            }
        }

        return $slug;
    }
}

if (!function_exists('format_post_date')) {
    /**
     * Format post date
     */
    function format_post_date($date, string $format = 'F j, Y'): string
    {
        if (!$date) {
            return '';
        }

        return date($format, strtotime($date));
    }
}

if (!function_exists('get_reading_time')) {
    /**
     * Calculate reading time
     */
    function get_reading_time(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $wordsPerMinute = 200;
        return max(1, ceil($wordCount / $wordsPerMinute));
    }
}