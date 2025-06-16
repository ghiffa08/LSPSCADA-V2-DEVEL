<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Production Authentication Configuration
 * Optimized settings for production environment
 */
class AuthProduction extends BaseConfig
{
    /**
     * Authentication settings optimized for production
     */
    public array $authSettings = [
        // Reduce debug logging in production
        'enableDebugLogging' => false,
        
        // Session settings
        'sessionTimeout' => 3600, // 1 hour
        'sessionRegenerateId' => true,
        'sessionRegenerateDestroy' => true,
        
        // Security settings
        'enableCSRF' => true,
        'csrfTokenName' => 'csrf_token',
        'csrfHeaderName' => 'X-CSRF-TOKEN',
        
        // Rate limiting
        'enableRateLimit' => true,
        'maxLoginAttempts' => 5,
        'rateLimitWindow' => 900, // 15 minutes
        
        // Password settings
        'passwordMinLength' => 8,
        'passwordRequireSpecialChar' => true,
        'passwordRequireNumber' => true,
        
        // OAuth settings
        'oauthTimeout' => 300, // 5 minutes
        'oauthCacheEnabled' => true,
        
        // Database optimization
        'enableQueryCaching' => true,
        'cacheAuthQueries' => true,
        'cacheTimeout' => 300,
        
        // Error handling
        'logAuthErrors' => true,
        'logSuccessfulLogins' => false, // Reduce log volume
        'logFailedLogins' => true,
        
        // Cleanup settings
        'cleanupExpiredSessions' => true,
        'cleanupExpiredTokens' => true,
        'cleanupInterval' => 3600, // 1 hour
    ];
    
    /**
     * Error messages for production (user-friendly)
     */
    public array $errorMessages = [
        'login_failed' => 'Invalid credentials. Please check your email and password.',
        'account_inactive' => 'Your account is not active. Please contact support.',
        'account_locked' => 'Your account has been temporarily locked due to multiple failed login attempts.',
        'session_expired' => 'Your session has expired. Please log in again.',
        'oauth_error' => 'Authentication failed. Please try again or contact support.',
        'system_error' => 'A system error occurred. Please try again later.',
        'validation_error' => 'Please check your input and try again.',
        'rate_limit_exceeded' => 'Too many attempts. Please try again later.',
    ];
    
    /**
     * Production redirect URLs
     */
    public array $redirectUrls = [
        'login_success' => '/dashboard',
        'login_failed' => '/login',
        'logout_success' => '/',
        'oauth_success' => '/dashboard',
        'oauth_failed' => '/login',
        'password_reset' => '/reset-password',
        'account_locked' => '/login',
    ];
    
    /**
     * Logging levels for production
     */
    public array $logLevels = [
        'auth_success' => 'info',
        'auth_failure' => 'warning',
        'oauth_success' => 'info',
        'oauth_failure' => 'warning',
        'system_error' => 'error',
        'security_issue' => 'critical',
    ];
}
