<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RateLimitFilter implements FilterInterface
{
    protected $cache;

    public function __construct()
    {
        $this->cache = Services::cache();
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        // Only apply to AJAX requests for user management
        if (!$request->isAJAX()) {
            return;
        }

        $currentUser = user();
        if (!$currentUser) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Unauthorized']);
        }

        // Get rate limit type from arguments or determine from route
        $limitType = $arguments[0] ?? $this->determineLimitType($request);
        $config = config('UserManagementCache');

        if (!isset($config->rateLimits[$limitType])) {
            return; // No rate limit configured
        }

        $limit = $config->rateLimits[$limitType]['limit'];
        $window = $config->rateLimits[$limitType]['window'];

        $key = "rate_limit_{$limitType}_{$currentUser->id}";
        $attempts = $this->cache->get($key) ?? 0;

        if ($attempts >= $limit) {
            $config->logging['log_rate_limit_hits'] &&
                log_message('warning', "Rate limit exceeded for user {$currentUser->id} on action {$limitType}");

            return Services::response()
                ->setStatusCode(429)
                ->setJSON([
                    'error' => 'Rate limit exceeded',
                    'message' => "Terlalu banyak permintaan. Tunggu {$window} detik.",
                    'retry_after' => $window
                ]);
        }

        // Increment counter
        $this->cache->save($key, $attempts + 1, $window);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do after
    }

    private function determineLimitType(RequestInterface $request): string
    {
        $uri = $request->getUri()->getPath();

        if (strpos($uri, '/create') !== false) {
            return 'user_create';
        }
        if (strpos($uri, '/delete') !== false) {
            return 'user_delete';
        }
        if (strpos($uri, '/update') !== false) {
            return 'user_update';
        }
        if (strpos($uri, '/batch') !== false) {
            return 'bulk_actions';
        }

        return 'general';
    }
}
