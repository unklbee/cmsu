<?php

namespace App\Modules\Blog\Models;

use App\Models\CMS\BaseModel;
use App\Modules\Blog\Entities\CategoryEntity;

class CategoryModel extends BaseModel
{
    protected $table = 'blog_categories';
    protected $primaryKey = 'id';
    protected $returnType = CategoryEntity::class;
    protected $allowedFields = [
        'parent_id', 'name', 'slug', 'description',
        'image', 'post_count', 'meta_title',
        'meta_description', 'meta_keywords'
    ];

    protected $validationRules = [
        'name' => 'required|string|max_length[100]',
        'slug' => 'required|string|is_unique[blog_categories.slug,id,{id}]'
    ];

    protected $beforeInsert = ['generateSlug'];
    protected $beforeUpdate = ['generateSlug'];
    protected $afterDelete = ['reassignPosts'];

    /**
     * Get category by slug
     */
    public function getBySlug(string $slug): ?CategoryEntity
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get categories tree
     */
    public function getTree($parentId = null): array
    {
        $categories = $this->where('parent_id', $parentId)
            ->orderBy('name', 'ASC')
            ->findAll();

        foreach ($categories as &$category) {
            $category->children = $this->getTree($category->id);
        }

        return $categories;
    }

    /**
     * Get categories for dropdown
     */
    public function getDropdown(): array
    {
        $categories = $this->orderBy('name', 'ASC')->findAll();
        $options = [];

        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }

        return $options;
    }

    /**
     * Update post count
     */
    public function updatePostCount(int $categoryId): bool
    {
        $count = model('App\Modules\Blog\Models\PostModel')
            ->where('category_id', $categoryId)
            ->where('status', 'published')
            ->countAllResults();

        return $this->update($categoryId, ['post_count' => $count]);
    }

    /**
     * Callbacks
     */
    protected function generateSlug(array $data)
    {
        if (isset($data['data']['name']) && !isset($data['data']['slug'])) {
            helper('text');
            $data['data']['slug'] = url_title($data['data']['name'], '-', true);
        }
        return $data;
    }

    protected function reassignPosts(array $data)
    {
        // Reassign posts to default category
        $defaultCategory = $this->where('slug', 'uncategorized')->first();
        if ($defaultCategory) {
            model('App\Modules\Blog\Models\PostModel')
                ->where('category_id', $data['id'])
                ->set(['category_id' => $defaultCategory->id])
                ->update();
        }
        return $data;
    }
}