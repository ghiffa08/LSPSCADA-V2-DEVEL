<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;

/**
 * CustomResponseService
 * 
 * Service for handling standardized API responses across the application
 * Provides consistent response formatting and error handling
 */
class CustomResponseService
{
    use ResponseTrait;

    /**
     * Success response with data
     *
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $code HTTP status code
     * @return ResponseInterface
     */
    public function success($data = null, string $message = 'Success', int $code = 200): ResponseInterface
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_hash' => csrf_hash()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $this->respond($response, $code);
    }

    /**
     * Error response
     *
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param mixed $errors Additional error details
     * @return ResponseInterface
     */
    public function error(string $message = 'An error occurred', int $code = 400, $errors = null): ResponseInterface
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'code' => $code,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_hash' => csrf_hash()
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $code);
    }

    /**
     * Validation error response
     *
     * @param array $validationErrors Validation error array
     * @param string $message Custom message
     * @return ResponseInterface
     */
    public function validationError(array $validationErrors, string $message = 'Validation failed'): ResponseInterface
    {
        return $this->error($message, 422, [
            'validation_errors' => $validationErrors,
            'field_count' => count($validationErrors)
        ]);
    }

    /**
     * Not found response
     *
     * @param string $message Not found message
     * @return ResponseInterface
     */
    public function notFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->error($message, 404);
    }

    /**
     * Unauthorized response
     *
     * @param string $message Unauthorized message
     * @return ResponseInterface
     */
    public function unauthorized(string $message = 'Unauthorized access'): ResponseInterface
    {
        return $this->error($message, 401);
    }

    /**
     * Forbidden response
     *
     * @param string $message Forbidden message
     * @return ResponseInterface
     */
    public function forbidden(string $message = 'Access forbidden'): ResponseInterface
    {
        return $this->error($message, 403);
    }

    /**
     * Internal server error response
     *
     * @param string $message Error message
     * @param mixed $debugInfo Debug information (only in development)
     * @return ResponseInterface
     */
    public function serverError(string $message = 'Internal server error', $debugInfo = null): ResponseInterface
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'code' => 500,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_hash' => csrf_hash()
        ];

        // Add debug info only in development environment
        if (ENVIRONMENT === 'development' && $debugInfo !== null) {
            $response['debug'] = $debugInfo;
        }

        return $this->respond($response, 500);
    }

    /**
     * Created response for successful resource creation
     *
     * @param mixed $data Created resource data
     * @param string $message Success message
     * @return ResponseInterface
     */
    public function created($data = null, string $message = 'Resource created successfully'): ResponseInterface
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Updated response for successful resource update
     *
     * @param mixed $data Updated resource data
     * @param string $message Success message
     * @return ResponseInterface
     */
    public function updated($data = null, string $message = 'Resource updated successfully'): ResponseInterface
    {
        return $this->success($data, $message, 200);
    }

    /**
     * Deleted response for successful resource deletion
     *
     * @param string $message Success message
     * @return ResponseInterface
     */
    public function deleted(string $message = 'Resource deleted successfully'): ResponseInterface
    {
        return $this->success(null, $message, 200);
    }

    /**
     * Paginated response for list data
     *
     * @param array $data List data
     * @param array $pagination Pagination metadata
     * @param string $message Success message
     * @return ResponseInterface
     */
    public function paginated(array $data, array $pagination, string $message = 'Data retrieved successfully'): ResponseInterface
    {
        return $this->success([
            'items' => $data,
            'pagination' => $pagination
        ], $message);
    }

    /**
     * DataTables response format
     *
     * @param array $data Table data
     * @param int $draw Draw counter
     * @param int $recordsTotal Total records without filtering
     * @param int $recordsFiltered Total records with filtering
     * @return ResponseInterface
     */
    public function dataTable(array $data, int $draw, int $recordsTotal, int $recordsFiltered): ResponseInterface
    {
        return $this->respond([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * File download response
     *
     * @param string $filePath Path to file
     * @param string $fileName Download filename
     * @param array $headers Additional headers
     * @return ResponseInterface
     */
    public function download(string $filePath, string $fileName = null, array $headers = []): ResponseInterface
    {
        if (!file_exists($filePath)) {
            return $this->notFound('File not found');
        }

        $fileName = $fileName ?: basename($filePath);
        $mimeType = mime_content_type($filePath);

        $defaultHeaders = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Length' => filesize($filePath),
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public'
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $response = service('response');

        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        $response->setBody(file_get_contents($filePath));

        return $response;
    }

    /**
     * JSON response for AJAX requests
     *
     * @param mixed $data Response data
     * @param bool $success Success status
     * @param string $message Response message
     * @param array $meta Additional metadata
     * @return ResponseInterface
     */
    public function ajax($data = null, bool $success = true, string $message = '', array $meta = []): ResponseInterface
    {
        $response = [
            'success' => $success,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_hash' => csrf_hash()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return $this->respond($response);
    }

    /**
     * Progress response for long-running operations
     *
     * @param int $progress Progress percentage (0-100)
     * @param string $message Progress message
     * @param array $data Additional data
     * @return ResponseInterface
     */
    public function progress(int $progress, string $message = '', array $data = []): ResponseInterface
    {
        $response = [
            'status' => 'progress',
            'progress' => max(0, min(100, $progress)), // Ensure 0-100 range
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'csrf_hash' => csrf_hash()
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        return $this->respond($response);
    }

    /**
     * Batch operation response
     *
     * @param array $results Array of operation results
     * @param string $message Overall message
     * @return ResponseInterface
     */
    public function batch(array $results, string $message = 'Batch operation completed'): ResponseInterface
    {
        $totalCount = count($results);
        $successCount = count(array_filter($results, fn($r) => $r['success'] ?? false));
        $errorCount = $totalCount - $successCount;

        return $this->success([
            'results' => $results,
            'summary' => [
                'total' => $totalCount,
                'success' => $successCount,
                'errors' => $errorCount,
                'success_rate' => $totalCount > 0 ? round(($successCount / $totalCount) * 100, 2) : 0
            ]
        ], $message);
    }

    /**
     * Export response for file exports
     *
     * @param string $exportType Export type (pdf, excel, csv, etc.)
     * @param string $filePath Generated file path
     * @param array $metadata Export metadata
     * @return ResponseInterface
     */
    public function export(string $exportType, string $filePath, array $metadata = []): ResponseInterface
    {
        return $this->success([
            'export_type' => $exportType,
            'file_path' => $filePath,
            'download_url' => base_url('downloads/' . basename($filePath)),
            'metadata' => array_merge([
                'generated_at' => date('Y-m-d H:i:s'),
                'file_size' => file_exists($filePath) ? filesize($filePath) : 0
            ], $metadata)
        ], 'Export generated successfully');
    }

    /**
     * Statistics response
     *
     * @param array $statistics Statistics data
     * @param string $message Response message
     * @param array $filters Applied filters
     * @return ResponseInterface
     */
    public function statistics(array $statistics, string $message = 'Statistics retrieved successfully', array $filters = []): ResponseInterface
    {
        return $this->success([
            'statistics' => $statistics,
            'filters' => $filters,
            'generated_at' => date('Y-m-d H:i:s')
        ], $message);
    }

    /**
     * Form validation response helper
     *
     * @param bool $isValid Validation result
     * @param array $errors Validation errors if any
     * @param array $data Valid data if validation passed
     * @param string $successMessage Success message
     * @param string $errorMessage Error message
     * @return ResponseInterface
     */
    public function validation(bool $isValid, array $errors = [], array $data = [], string $successMessage = 'Validation passed', string $errorMessage = 'Validation failed'): ResponseInterface
    {
        if ($isValid) {
            return $this->success($data, $successMessage);
        } else {
            return $this->validationError($errors, $errorMessage);
        }
    }

    /**
     * Cache response with cache metadata
     *
     * @param mixed $data Cached data
     * @param array $cacheInfo Cache information
     * @param string $message Response message
     * @return ResponseInterface
     */
    public function cached($data, array $cacheInfo = [], string $message = 'Data retrieved from cache'): ResponseInterface
    {
        return $this->success($data, $message, 200, [
            'cache' => array_merge([
                'cached' => true,
                'cache_time' => date('Y-m-d H:i:s')
            ], $cacheInfo)
        ]);
    }

    /**
     * Health check response
     *
     * @param array $healthData Health check data
     * @param bool $healthy Overall health status
     * @return ResponseInterface
     */
    public function health(array $healthData, bool $healthy = true): ResponseInterface
    {
        return $this->respond([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'health_data' => $healthData
        ], $healthy ? 200 : 503);
    }
}
