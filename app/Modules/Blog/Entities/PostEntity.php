<?php

namespace App\Modules\Blog\Entities;

use CodeIgniter\Entity\Entity;

class PostEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'published_at'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'category_id' => 'integer',
        'featured_image' => '?integer',
        'views' => 'integer',
        'allow_comments' => 'boolean',
        'meta_keywords' => '?json-array'
    ];

    /**
     * Get post URL
     */
    public function getUrl(): string
    {
        return site_url('blog/post/' . $this->slug);
    }

    /**
     * Get category
     */
    public function getCategory()
    {
        return model('App\Modules\Blog\Models\CategoryModel')->find($this->category_id);
    }

    /**
     * Get tags
     */
    public function getTags(): array
    {
        return model('App\Modules\Blog\Models\TagModel')->getByPost($this->id);
    }

    /**
     * Get author
     */
    public function getAuthor()
    {
        $users = model('UserModel');
        return $users->find($this->user_id);
    }

    /**
     * Get featured image URL
     */
    public function getFeaturedImageUrl(string $size = 'medium'): string
    {
        if ($this->featured_image) {
            return media_url($this->featured_image, $size);
        }
        return base_url('assets/img/default-post.jpg');
    }

    /**
     * Check if published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' &&
            $this->published_at &&
            strtotime($this->published_at) <= time();
    }

    /**
     * Get excerpt
     */
    public function getExcerpt(int $length = 200): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        // Generate from content
        $text = strip_tags($this->content);
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }

    /**
     * Get reading time in minutes
     */
    public function getReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $wordsPerMinute = 200;
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    /**
     * Get formatted publish date
     */
    public function getPublishDate(string $format = 'F j, Y'): string
    {
        if (!$this->published_at) {
            return '';
        }
        return date($format, strtotime($this->published_at));
    }
}