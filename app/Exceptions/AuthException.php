<?php

namespace App\Exceptions;

use Exception;

/**
 * Auth Exception
 * 
 * Custom exception for authentication operations
 * 
 * @package App\Exceptions
 */
class AuthException extends Exception
{
    /**
     * Create authentication required exception
     * 
     * @return self
     */
    public static function required(): self
    {
        return new self('Authentication required', 401);
    }

    /**
     * Create invalid credentials exception
     * 
     * @return self
     */
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials', 401);
    }

    /**
     * Create insufficient permissions exception
     * 
     * @param string $permission
     * @return self
     */
    public static function insufficientPermissions(string $permission = ''): self
    {
        $message = 'Insufficient permissions';
        if ($permission) {
            $message .= ' for: ' . $permission;
        }
        return new self($message, 403);
    }

    /**
     * Create asesor not found exception
     * 
     * @return self
     */
    public static function asesorNotFound(): self
    {
        return new self('Asesor data not found for current user', 404);
    }
}
