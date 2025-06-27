<?php

namespace App\Services;

use CodeIgniter\Cache\CacheInterface;

/**
 * Optimized Caching Service for LSP SCADA System
 * Handles intelligent caching with invalidation strategies
 */
class OptimizedCacheService
{
    private CacheInterface $cache;
    private array $cacheGroups = [
        'skema' => 3600,      // 1 hour - skema data changes rarely
        'asesor' => 1800,     // 30 minutes - asesor data changes occasionally  
        'observasi' => 600,   // 10 minutes - observasi data changes frequently
        'structure' => 7200,  // 2 hours - structure data very stable
        'statistics' => 300   // 5 minutes - statistics need frequent updates
    ];

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    /**
     * Get cached data with automatic refresh strategy
     */
    public function get(string $key, string $group = 'default'): mixed
    {
        $fullKey = $this->buildKey($key, $group);
        return $this->cache->get($fullKey);
    }

    /**
     * Save data to cache with appropriate TTL
     */
    public function save(string $key, mixed $data, string $group = 'default', ?int $ttl = null): bool
    {
        $fullKey = $this->buildKey($key, $group);
        $ttl = $ttl ?? $this->cacheGroups[$group] ?? 600;
        
        return $this->cache->save($fullKey, $data, $ttl);
    }

    /**
     * Remember pattern - get from cache or execute callback
     */
    public function remember(string $key, callable $callback, string $group = 'default', ?int $ttl = null): mixed
    {
        $data = $this->get($key, $group);
        
        if ($data === null) {
            $data = $callback();
            $this->save($key, $data, $group, $ttl);
        }
        
        return $data;
    }

    /**
     * Invalidate cache by group or specific key
     */
    public function invalidate(string $keyOrGroup, bool $isGroup = false): void
    {
        if ($isGroup) {
            $this->invalidateGroup($keyOrGroup);
        } else {
            $this->cache->delete($keyOrGroup);
        }
    }

    /**
     * Invalidate entire cache group
     */
    public function invalidateGroup(string $group): void
    {
        // For CodeIgniter 4, we need to manually track and delete group items
        // Alternative: use Redis with key patterns or implement group tracking
        $groupKey = "lsp_cache_groups_{$group}";
        $groupItems = $this->cache->get($groupKey) ?? [];
        
        foreach ($groupItems as $item) {
            $this->cache->delete($item);
        }
        
        $this->cache->delete($groupKey);
    }

    /**
     * Cache warming for frequently accessed data
     */
    public function warmUpCache(): void
    {
        $this->warmUpSkemaStructures();
        $this->warmUpAsesorData();
        $this->warmUpStatistics();
    }

    /**
     * Build standardized cache key
     */
    private function buildKey(string $key, string $group): string
    {
        return "lsp_cache_{$group}_{$key}";
    }

    /**
     * Warm up skema structure cache
     */
    private function warmUpSkemaStructures(): void
    {
        $db = \Config\Database::connect();
        $skemas = $db->table('skema')->select('id_skema')->where('status', 'Y')->get()->getResultArray();
        
        $observasiModel = new \App\Models\ObservasiModel();
        
        foreach ($skemas as $skema) {
            // Pre-cache skema structures
            $observasiModel->getStrukturObservasiSkema($skema['id_skema']);
        }
    }

    /**
     * Warm up asesor data cache
     */
    private function warmUpAsesorData(): void
    {
        $db = \Config\Database::connect();
        $asesors = $db->table('asesor')->select('id_asesor')->get()->getResultArray();
        
        $observasiModel = new \App\Models\ObservasiModel();
        
        foreach ($asesors as $asesor) {
            // Pre-cache asesor competencies
            $observasiModel->getAsesorWithAllSkema($asesor['id_asesor']);
        }
    }

    /**
     * Warm up statistics cache
     */
    private function warmUpStatistics(): void
    {
        // Cache frequently accessed statistics
        $this->remember('dashboard_stats', function() {
            $db = \Config\Database::connect();
            return [
                'total_asesor' => $db->table('asesor')->countAllResults(),
                'total_asesi' => $db->table('asesi')->countAllResults(),
                'total_observasi' => $db->table('observasi')->countAllResults(),
                'total_skema' => $db->table('skema')->where('status', 'Y')->countAllResults()
            ];
        }, 'statistics');
    }
}
