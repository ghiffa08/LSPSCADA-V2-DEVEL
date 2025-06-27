<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;

/**
 * Cache Service
 * 
 * Wrapper for cache operations with environment awareness
 * 
 * @package App\Services
 */
class CacheService
{
    private CacheInterface $cache;
    private bool $enabled;

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
        $this->enabled = ENVIRONMENT === 'production' || ENVIRONMENT === 'testing';
    }

    /**
     * Save data to cache
     * 
     * @param string $key
     * @param mixed $data
     * @param int $ttl Time to live in seconds
     * @return bool
     */
    public function save(string $key, $data, int $ttl = 3600): bool
    {
        if (!$this->enabled) {
            return true; // Skip caching in development
        }

        return $this->cache->save($key, $data, $ttl);
    }

    /**
     * Get data from cache
     * 
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (!$this->enabled) {
            return null; // Skip caching in development
        }

        return $this->cache->get($key);
    }

    /**
     * Delete data from cache
     * 
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        if (!$this->enabled) {
            return true; // Skip caching in development
        }

        return $this->cache->delete($key);
    }

    /**
     * Check if key exists in cache
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        if (!$this->enabled) {
            return false; // Skip caching in development
        }

        return $this->get($key) !== null;
    }

    /**
     * Clear all cache
     * 
     * @return bool
     */
    public function clean(): bool
    {
        return $this->cache->clean();
    }

    /**
     * Get cache info
     * 
     * @return array
     */
    public function getCacheInfo(): array
    {
        return $this->cache->getCacheInfo();
    }

    /**
     * Remember: Get from cache or execute callback and cache result
     * 
     * @param string $key
     * @param callable $callback
     * @param int $ttl
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $data = $this->get($key);

        if ($data === null) {
            $data = $callback();
            $this->save($key, $data, $ttl);
        }

        return $data;
    }

    /**
     * Flush cache with pattern
     * 
     * @param string $pattern
     * @return bool
     */
    public function flush(string $pattern): bool
    {
        // This is implementation dependent
        // For file cache, you might need to scan directories
        // For Redis, you can use SCAN with pattern

        return $this->cache->clean();
    }
}
