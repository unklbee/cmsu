<?php

namespace App\Entities\CMS;

use CodeIgniter\Entity\Entity;

class MediaEntity extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at'];
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'size' => 'integer',
        'is_public' => 'boolean',
        'metadata' => 'json'
    ];

    /**
     * Get human readable size
     */
    public function getSizeFormatted(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnail(string $size = 'medium'): string
    {
        if ($this->type !== 'image') {
            return $this->getTypeIcon();
        }

        if (isset($this->metadata['thumbnails'][$size])) {
            $thumbPath = $this->folder . '/' . $this->metadata['thumbnails'][$size];
            return $this->is_public
                ? base_url('uploads/' . $thumbPath)
                : site_url('media/file/' . base64_encode($thumbPath));
        }

        return $this->url;
    }

    /**
     * Get file icon based on type
     */
    public function getTypeIcon(): string
    {
        $icons = [
            'image' => 'fa-image',
            'video' => 'fa-video',
            'audio' => 'fa-music',
            'document' => 'fa-file-alt',
            'other' => 'fa-file'
        ];

        return $icons[$this->type] ?? 'fa-file';
    }

    /**
     * Check if media is image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Get download URL
     */
    public function getDownloadUrl(): string
    {
        return site_url('media/download/' . $this->id);
    }

    /**
     * Get metadata value
     */
    public function getMeta(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }
}