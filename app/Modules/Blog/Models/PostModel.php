<?php

// Post Model
// File: app/Modules/Blog/Models/PostModel.php
namespace App\Modules\Blog\Models;

use App\Models\CMS\BaseModel;
use App\Modules\Blog\Entities\PostEntity;

class PostModel extends BaseModel
{
    protected $table = 'blog_posts';
    protected $primaryKey = 'id';
    protected $returnType = PostEntity::class;
    protected $allowedFields = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt',
        'content', 'featured_image', 'status', 'published_at',
        'views', 'allow_comments', 'meta_title', 'meta_description',
        'meta_keywords'
    ];

    protected $validationRules = [
        'title' => 'required|string|max_length[255]',
        'slug' => 'required|string|is_unique[blog_posts.slug,id,{id}]',
        'content' => 'required|string',
        'category_id' => 'required|numeric',
        'status' => 'required|in_list[draft,published,scheduled]'
    ];

    protected $beforeInsert = ['generateSlug', 'setAuthor'];
    protected $afterInsert = ['updateCategoryCount', 'notifySubscribers'];
    protected $afterUpdate = ['clearBlogCache'];
    protected $afterDelete = ['updateCategoryCount', 'deleteComments'];

    /**
     * Get published posts
     */
    public function getPublished(int $limit = 10, int $offset = 0)
    {
        return $this->where('status', 'published')
            ->where('published_at <=', date('Y-m-d H:i:s'))
            ->orderBy('published_at', 'DESC')
            ->limit($limit, $offset)
            ->findAll();
    }

    /**
     * Get post by slug
     */
    public function getBySlug(string $slug): ?PostEntity
    {
        return $this->where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    /**
     * Get posts by category
     */
    public function getByCategory(int $categoryId, int $limit = 10)
    {
        return $this->where('category_id', $categoryId)
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get posts by tag
     */
    public function getByTag(int $tagId, int $limit = 10)
    {
        return $this->select('blog_posts.*')
            ->join('blog_post_tags', 'blog_post_tags.post_id = blog_posts.id')
            ->where('blog_post_tags.tag_id', $tagId)
            ->where('blog_posts.status', 'published')
            ->orderBy('blog_posts.published_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Search posts
     */
    public function search(string|array $keyword, int|array $limit = 10)
    {
        return $this->groupStart()
            ->like('title', $keyword)
            ->orLike('content', $keyword)
            ->orLike('excerpt', $keyword)
            ->groupEnd()
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get related posts
     */
    public function getRelated(int $postId, int $categoryId, int $limit = 5)
    {
        return $this->where('id !=', $postId)
            ->where('category_id', $categoryId)
            ->where('status', 'published')
            ->orderBy('RAND()')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Update view count
     */
    public function incrementViews(int $postId): bool
    {
        return $this->where('id', $postId)
            ->set('views', 'views + 1', false)
            ->update();
    }

    /**
     * Callbacks
     */
    protected function generateSlug(array $data)
    {
        if (!isset($data['data']['slug']) && isset($data['data']['title'])) {
            $data['data']['slug'] = generate_slug($data['data']['title']);
        }
        return $data;
    }

    protected function setAuthor(array $data)
    {
        if (!isset($data['data']['user_id'])) {
            $data['data']['user_id'] = user_id();
        }
        return $data;
    }

    protected function updateCategoryCount(array $data)
    {
        // Update category post count
        return $data;
    }

    protected function notifySubscribers(array $data)
    {
        // Send notifications to subscribers
        return $data;
    }

    protected function clearBlogCache(array $data)
    {
        clear_cms_cache('blog');
        return $data;
    }

    protected function deleteComments(array $data)
    {
        // Delete related comments
        return $data;
    }
}