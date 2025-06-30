<?php

namespace App\Modules\Blog\Entities;

use CodeIgniter\Entity\Entity;

class TagEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'post_count' => 'integer'
    ];

    /**
     * Get tag URL
     */
    public function getUrl(): string
    {
        return site_url('blog/tag/' . $this->slug);
    }

    /**
     * Get posts with this tag
     */
    public function getPosts(int $limit = 10): array
    {
        return model('App\Modules\Blog\Models\PostModel')
            ->getByTag($this->id, $limit);
    }

    /**
     * Get tag size for tag cloud
     */
    public function getCloudSize(int $min = 12, int $max = 32): int
    {
        // Calculate size based on post count
        $maxCount = model('App\Modules\Blog\Models\TagModel')->getMaxPostCount();
        if ($maxCount == 0) return $min;

        $ratio = $this->post_count / $maxCount;
        return round($min + ($max - $min) * $ratio);
    }
}