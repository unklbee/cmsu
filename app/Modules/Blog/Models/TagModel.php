<?php

namespace App\Modules\Blog\Models;

use App\Models\CMS\BaseModel;
use App\Modules\Blog\Entities\TagEntity;

class TagModel extends BaseModel
{
    protected $table = 'blog_tags';
    protected $primaryKey = 'id';
    protected $returnType = TagEntity::class;
    protected $allowedFields = ['name', 'slug', 'post_count'];

    protected $validationRules = [
        'name' => 'required|string|max_length[50]',
        'slug' => 'required|string|is_unique[blog_tags.slug,id,{id}]'
    ];

    protected $beforeInsert = ['generateSlug'];
    protected $beforeUpdate = ['generateSlug'];

    /**
     * Get tag by slug
     */
    public function getBySlug(string $slug): ?TagEntity
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get popular tags
     */
    public function getPopular(int $limit = 20): array
    {
        return $this->orderBy('post_count', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get tags by post
     */
    public function getByPost(int $postId): array
    {
        return $this->select('blog_tags.*')
            ->join('blog_post_tags', 'blog_post_tags.tag_id = blog_tags.id')
            ->where('blog_post_tags.post_id', $postId)
            ->findAll();
    }

    /**
     * Sync tags for post
     */
    public function syncPostTags(int $postId, array $tagNames): void
    {
        $db = \Config\Database::connect();

        // Delete existing tags
        $db->table('blog_post_tags')->where('post_id', $postId)->delete();

        if (empty($tagNames)) {
            return;
        }

        // Process each tag
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            // Find or create tag
            $tag = $this->where('name', $tagName)->first();
            if (!$tag) {
                $this->insert([
                    'name' => $tagName,
                    'slug' => url_title($tagName, '-', true)
                ]);
                $tagId = $this->insertID;
            } else {
                $tagId = $tag->id;
            }

            // Insert relationship
            $db->table('blog_post_tags')->insert([
                'post_id' => $postId,
                'tag_id' => $tagId
            ]);
        }

        // Update post counts
        $this->updatePostCounts();
    }

    /**
     * Update post counts for all tags
     */
    public function updatePostCounts(): void
    {
        $db = \Config\Database::connect();
        $builder = $db->table('blog_tags t');

        $builder->set('t.post_count', '(
            SELECT COUNT(*) 
            FROM blog_post_tags pt 
            JOIN blog_posts p ON pt.post_id = p.id 
            WHERE pt.tag_id = t.id 
            AND p.status = "published"
        )', false);

        $builder->update();
    }

    /**
     * Get max post count
     */
    public function getMaxPostCount(): int
    {
        $result = $this->selectMax('post_count')->first();
        return $result ? (int)$result->post_count : 0;
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
}