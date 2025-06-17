<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class UserManagementCache extends BaseConfig
{
    /**
     * Cache configuration for user management system
     */

    // Cache TTL settings (in seconds)
    public array $cacheTTL = [
        'user_stats' => 300,        // 5 minutes
        'available_roles' => 900,   // 15 minutes
        'user_count' => 60,         // 1 minute
        'group_counts' => 300,      // 5 minutes
        'deleted_stats' => 180,     // 3 minutes
    ];

    // Cache key prefixes
    public array $cacheKeys = [
        'stats' => 'user_management_stats',
        'roles' => 'available_roles',
        'user_count' => 'total_users_count',
        'group_prefix' => 'user_count_group_',
        'rate_limit_prefix' => [
            'create' => 'user_create_',
            'delete' => 'user_delete_',
            'update' => 'user_update_',
        ]
    ];

    // Rate limiting settings
    public array $rateLimits = [
        'user_create' => ['limit' => 5, 'window' => 60],    // 5 creates per minute
        'user_delete' => ['limit' => 10, 'window' => 60],   // 10 deletes per minute
        'user_update' => ['limit' => 20, 'window' => 60],   // 20 updates per minute
        'bulk_actions' => ['limit' => 3, 'window' => 300],  // 3 bulk actions per 5 minutes
    ];

    // Security settings
    public array $security = [
        'max_search_length' => 100,
        'min_search_length' => 2,
        'max_records_per_page' => 100,
        'default_records_per_page' => 25,
        'allowed_roles' => ['Admin', 'Asesor', 'Asesi'],
        'protected_roles' => ['Super Admin'],
    ];

    // Performance settings
    public array $performance = [
        'enable_query_cache' => true,
        'enable_result_cache' => true,
        'max_concurrent_operations' => 5,
        'query_timeout' => 30,
    ];

    // Logging settings
    public array $logging = [
        'log_user_creation' => true,
        'log_user_deletion' => true,
        'log_user_updates' => true,
        'log_bulk_actions' => true,
        'log_failed_attempts' => true,
        'log_rate_limit_hits' => true,
    ];
}
