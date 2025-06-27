<?php

namespace App\Utils;

/**
 * API Response Utility
 * 
 * Standardized API response format
 * 
 * @package App\Utils
 */
class ApiResponse
{
    /**
     * Success response
     * 
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): array
    {
        $response = [
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }

    /**
     * Error response
     * 
     * @param string $message
     * @param mixed $errors
     * @param int $code
     * @return array
     */
    public static function error(string $message = 'Error', $errors = null, int $code = 400): array
    {
        $response = [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'timestamp' => date('c')
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Validation error response
     * 
     * @param array $errors
     * @param string $message
     * @return array
     */
    public static function validation(array $errors, string $message = 'Validation failed'): array
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Not found response
     * 
     * @param string $message
     * @return array
     */
    public static function notFound(string $message = 'Resource not found'): array
    {
        return self::error($message, null, 404);
    }

    /**
     * Unauthorized response
     * 
     * @param string $message
     * @return array
     */
    public static function unauthorized(string $message = 'Unauthorized'): array
    {
        return self::error($message, null, 401);
    }

    /**
     * Forbidden response
     * 
     * @param string $message
     * @return array
     */
    public static function forbidden(string $message = 'Forbidden'): array
    {
        return self::error($message, null, 403);
    }

    /**
     * Server error response
     * 
     * @param string $message
     * @return array
     */
    public static function serverError(string $message = 'Internal server error'): array
    {
        return self::error($message, null, 500);
    }

    /**
     * Paginated response
     * 
     * @param array $data
     * @param array $pagination
     * @param string $message
     * @return array
     */
    public static function paginated(array $data, array $pagination, string $message = 'Success'): array
    {
        return self::success([
            'items' => $data,
            'pagination' => $pagination
        ], $message);
    }
}
