<?php

namespace App\Modules\Blog\Entities;

use CodeIgniter\Entity\Entity;

class CategoryEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'parent_id' => '?integer',
        'post_count' => 'integer'
    ];

    /**
     * Get category URL
     */
    public function getUrl(): string
    {
        return site_url('blog/category/' . $this->slug);
    }

    /**
     * Get parent category
     */
    public function getParent()
    {
        if (!$this->parent_id) {
            return null;
        }
        return model('App\Modules\Blog\Models\CategoryModel')->find($this->parent_id);
    }

    /**
     * Get child categories
     */
    public function getChildren(): array
    {
        return model('App\Modules\Blog\Models\CategoryModel')
            ->where('parent_id', $this->id)
            ->findAll();
    }

    /**
     * Get posts in this category
     */
    public function getPosts(int $limit = 10): array
    {
        return model('App\Modules\Blog\Models\PostModel')
            ->getByCategory($this->id, $limit);
    }

    /**
     * Get full path (with parent categories)
     */
    public function getPath(): array
    {
        $path = [$this];
        $parent = $this->getParent();

        while ($parent) {
            array_unshift($path, $parent);
            $parent = $parent->getParent();
        }

        return $path;
    }
}