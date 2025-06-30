<?php

namespace App\Modules\Blog\Models;

use App\Models\CMS\BaseModel;
use App\Modules\Blog\Entities\CommentEntity;

class CommentModel extends BaseModel
{
    protected $table = 'blog_comments';
    protected $primaryKey = 'id';
    protected $returnType = CommentEntity::class;
    protected $allowedFields = [
        'post_id', 'parent_id', 'user_id', 'author_name',
        'author_email', 'author_url', 'content', 'status',
        'ip_address', 'user_agent'
    ];

    protected $validationRules = [
        'post_id' => 'required|numeric',
        'author_name' => 'required|string|max_length[100]',
        'author_email' => 'required|valid_email',
        'content' => 'required|string|min_length[10]',
        'status' => 'in_list[pending,approved,spam,trash]'
    ];

    /**
     * Get comments by post
     */
    public function getByPost(int $postId, bool $approvedOnly = true): array
    {
        $query = $this->where('post_id', $postId)
            ->where('parent_id', null);

        if ($approvedOnly) {
            $query->where('status', 'approved');
        }

        return $query->orderBy('created_at', 'ASC')->findAll();
    }

    /**
     * Get comment replies
     */
    public function getReplies(int $commentId, bool $approvedOnly = true): array
    {
        $query = $this->where('parent_id', $commentId);

        if ($approvedOnly) {
            $query->where('status', 'approved');
        }

        return $query->orderBy('created_at', 'ASC')->findAll();
    }

    /**
     * Get recent comments
     */
    public function getRecent(int $limit = 10): array
    {
        return $this->where('status', 'approved')
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get pending comments count
     */
    public function getPendingCount(): int
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    /**
     * Approve comment
     */
    public function approve(int $commentId): bool
    {
        return $this->update($commentId, ['status' => 'approved']);
    }

    /**
     * Mark as spam
     */
    public function markAsSpam(int $commentId): bool
    {
        return $this->update($commentId, ['status' => 'spam']);
    }

    /**
     * Get comment tree for a post
     */
    public function getTree(int $postId): array
    {
        $comments = $this->getByPost($postId);

        foreach ($comments as &$comment) {
            $comment->replies = $this->buildReplyTree($comment->id);
        }

        return $comments;
    }

    /**
     * Build reply tree recursively
     */
    private function buildReplyTree(int $parentId): array
    {
        $replies = $this->getReplies($parentId);

        foreach ($replies as &$reply) {
            $reply->replies = $this->buildReplyTree($reply->id);
        }

        return $replies;
    }
}