<?php

namespace App\Services;

/**
 * Performance Monitoring Service
 * Tracks query performance and provides optimization recommendations
 */
class PerformanceMonitorService
{
    private array $queryLog = [];
    private float $startTime;
    private array $slowQueries = [];
    private const SLOW_QUERY_THRESHOLD = 1.0; // 1 second

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    /**
     * Start monitoring a query
     */
    public function startQuery(string $query, array $params = []): string
    {
        $queryId = uniqid('query_');
        
        $this->queryLog[$queryId] = [
            'query' => $query,
            'params' => $params,
            'start_time' => microtime(true),
            'memory_start' => memory_get_usage(true)
        ];

        return $queryId;
    }

    /**
     * End monitoring a query
     */
    public function endQuery(string $queryId): void
    {
        if (!isset($this->queryLog[$queryId])) {
            return;
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $this->queryLog[$queryId]['end_time'] = $endTime;
        $this->queryLog[$queryId]['duration'] = $endTime - $this->queryLog[$queryId]['start_time'];
        $this->queryLog[$queryId]['memory_used'] = $endMemory - $this->queryLog[$queryId]['memory_start'];

        // Track slow queries
        if ($this->queryLog[$queryId]['duration'] > self::SLOW_QUERY_THRESHOLD) {
            $this->slowQueries[] = $this->queryLog[$queryId];
        }
    }

    /**
     * Get performance statistics
     */
    public function getStats(): array
    {
        $totalQueries = count($this->queryLog);
        $totalTime = 0;
        $totalMemory = 0;
        
        foreach ($this->queryLog as $query) {
            if (isset($query['duration'])) {
                $totalTime += $query['duration'];
                $totalMemory += $query['memory_used'] ?? 0;
            }
        }

        return [
            'total_queries' => $totalQueries,
            'total_time' => round($totalTime, 4),
            'average_time' => $totalQueries > 0 ? round($totalTime / $totalQueries, 4) : 0,
            'total_memory' => $this->formatBytes($totalMemory),
            'slow_queries' => count($this->slowQueries),
            'page_load_time' => round(microtime(true) - $this->startTime, 4)
        ];
    }

    /**
     * Get slow query analysis
     */
    public function getSlowQueryAnalysis(): array
    {
        $analysis = [];
        
        foreach ($this->slowQueries as $query) {
            $analysis[] = [
                'query' => $this->sanitizeQuery($query['query']),
                'duration' => round($query['duration'], 4),
                'memory' => $this->formatBytes($query['memory_used'] ?? 0),
                'recommendations' => $this->analyzeQuery($query['query'])
            ];
        }

        return $analysis;
    }

    /**
     * Analyze query and provide optimization recommendations
     */
    private function analyzeQuery(string $query): array
    {
        $recommendations = [];
        $queryLower = strtolower($query);

        // Check for common performance issues
        if (strpos($queryLower, 'select *') !== false) {
            $recommendations[] = 'Avoid SELECT * - specify only needed columns';
        }

        if (strpos($queryLower, 'left join') !== false && strpos($queryLower, 'where') === false) {
            $recommendations[] = 'Consider adding WHERE conditions to reduce JOIN result set';
        }

        if (preg_match('/order by.*limit/i', $query) && strpos($queryLower, 'index') === false) {
            $recommendations[] = 'Ensure ORDER BY columns are indexed for better LIMIT performance';
        }

        if (strpos($queryLower, 'group by') !== false && strpos($queryLower, 'having') === false) {
            $recommendations[] = 'Consider using WHERE instead of HAVING when possible';
        }

        if (preg_match('/like\s+[\'"]%.*%[\'"]/i', $query)) {
            $recommendations[] = 'Leading wildcard in LIKE prevents index usage - consider full-text search';
        }

        if (strpos($queryLower, 'distinct') !== false) {
            $recommendations[] = 'DISTINCT can be expensive - ensure it\'s necessary';
        }

        return $recommendations;
    }

    /**
     * Sanitize query for logging (remove sensitive data)
     */
    private function sanitizeQuery(string $query): string
    {
        // Remove actual values for security
        $query = preg_replace("/'\w+'/", "'***'", $query);
        $query = preg_replace('/\b\d+\b/', '?', $query);
        
        return $query;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Log performance data to file
     */
    public function logToFile(): void
    {
        $stats = $this->getStats();
        $slowQueries = $this->getSlowQueryAnalysis();
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'url' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
            'stats' => $stats,
            'slow_queries' => $slowQueries
        ];

        $logFile = WRITEPATH . 'logs/performance_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Get recommendations for overall performance improvement
     */
    public function getGeneralRecommendations(): array
    {
        $stats = $this->getStats();
        $recommendations = [];

        if ($stats['total_queries'] > 20) {
            $recommendations[] = [
                'issue' => 'High query count (' . $stats['total_queries'] . ')',
                'solution' => 'Implement eager loading and query optimization',
                'priority' => 'high'
            ];
        }

        if ($stats['page_load_time'] > 2.0) {
            $recommendations[] = [
                'issue' => 'Slow page load time (' . $stats['page_load_time'] . 's)',
                'solution' => 'Enable caching and optimize database queries',
                'priority' => 'high'
            ];
        }

        if ($stats['slow_queries'] > 0) {
            $recommendations[] = [
                'issue' => $stats['slow_queries'] . ' slow queries detected',
                'solution' => 'Optimize slow queries with proper indexing',
                'priority' => 'medium'
            ];
        }

        if ($stats['average_time'] > 0.1) {
            $recommendations[] = [
                'issue' => 'High average query time (' . $stats['average_time'] . 's)',
                'solution' => 'Review query complexity and add appropriate indexes',
                'priority' => 'medium'
            ];
        }

        return $recommendations;
    }
}
