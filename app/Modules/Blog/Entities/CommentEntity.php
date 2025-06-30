<?php

namespace App\Modules\Blog\Entities;

use CodeIgniter\Entity\Entity;

class CommentEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'post_id' => 'integer',
        'parent_id' => '?integer',
        'user_id' => '?integer'
    ];

    /**
     * Get post
     */
    public function getPost()
    {
        return model('App\Modules\Blog\Models\PostModel')->find($this->post_id);
    }

    /**
     * Get parent comment
     */
    public function getParent()
    {
        if (!$this->parent_id) {
            return null;
        }
        return model('App\Modules\Blog\Models\CommentModel')->find($this->parent_id);
    }

    /**
     * Get author
     */
    public function getAuthor()
    {
        if ($this->user_id) {
            return model('UserModel')->find($this->user_id);
        }

        // Return guest author info
        return (object)[
            'username' => $this->author_name,
            'email' => $this->author_email,
            'avatar' => $this->getAvatarUrl()
        ];
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrl(int $size = 64): string
    {
        $email = $this->author_email ?? 'guest@example.com';
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=mp";
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get formatted date
     */
    public function getDate(string $format = 'F j, Y \a\t g:i a'): string
    {
        return date($format, strtotime($this->created_at));
    }
}