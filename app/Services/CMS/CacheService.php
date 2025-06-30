<?php

/**
 * Cache Service - Advanced caching with tags
 */

namespace App\Services\CMS;

class CacheService
{
    protected $cache;
    protected $prefix = 'cms_';
    protected $tagPrefix = 'tag_';

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    /**
     * Get cache with tags
     */
    public function get(string $key, array $tags = [])
    {
        $fullKey = $this->prefix . $key;

        // Check if any tag is invalidated
        foreach ($tags as $tag) {
            if ($this->isTagInvalidated($tag)) {
                return null;
            }
        }

        return $this->cache->get($fullKey);
    }

    /**
     * Save cache with tags
     */
    public function save(string $key, $value, int $ttl = 300, array $tags = []): bool
    {
        $fullKey = $this->prefix . $key;

        // Save the cache
        $result = $this->cache->save($fullKey, $value, $ttl);

        // Register tags
        foreach ($tags as $tag) {
            $this->addKeyToTag($tag, $fullKey, $ttl);
        }

        return $result;
    }

    /**
     * Delete cache
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($this->prefix . $key);
    }

    /**
     * Flush caches by tag
     */
    public function flushTag(string $tag): bool
    {
        $tagKey = $this->tagPrefix . $tag;
        $keys = $this->cache->get($tagKey) ?? [];

        // Delete all keys with this tag
        foreach ($keys as $key) {
            $this->cache->delete($key);
        }

        // Invalidate tag
        $this->cache->save($tagKey . '_invalidated', time(), 86400);

        return true;
    }

    /**
     * Remember - Get from cache or execute callback
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = [])
    {
        $value = $this->get($key, $tags);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->save($key, $value, $ttl, $tags);

        return $value;
    }

    /**
     * Check if tag is invalidated
     */
    protected function isTagInvalidated(string $tag): bool
    {
        $invalidatedTime = $this->cache->get($this->tagPrefix . $tag . '_invalidated');
        return $invalidatedTime !== null;
    }

    /**
     * Add key to tag group
     */
    protected function addKeyToTag(string $tag, string $key, int $ttl): void
    {
        $tagKey = $this->tagPrefix . $tag;
        $keys = $this->cache->get($tagKey) ?? [];

        if (!in_array($key, $keys)) {
            $keys[] = $key;
            $this->cache->save($tagKey, $keys, $ttl);
        }
    }
}
