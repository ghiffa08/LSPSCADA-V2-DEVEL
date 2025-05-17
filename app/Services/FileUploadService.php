<?php

namespace App\Services;

/**
 * FileUploadService - Handles file uploads in a clean, optimized way
 * 
 * This service encapsulates all file upload logic for the application,
 * providing methods to validate, process, and store uploaded files securely.
 */
class FileUploadService implements FileUploadInterface
{
    /**
     * Base upload directory within the writable folder
     */
    protected string $baseUploadDir;

    /**
     * Constructor - sets up the base upload directory
     */
    public function __construct()
    {
        $this->baseUploadDir = WRITEPATH . 'uploads/';
    }

    /**
     * Upload multiple files at once
     * 
     * @param array $files Array of CodeIgniter file objects
     * @param array $configs Configuration for each file
     * @return array Results of each upload operation
     */
    public function uploadMultipleFiles(array $files, array $configs): array
    {
        $results = [];

        foreach ($files as $field => $file) {
            if (!isset($configs[$field])) {
                $results[$field] = [
                    'success' => false,
                    'error' => 'Missing configuration'
                ];
                continue;
            }

            $results[$field] = $this->uploadSingleFile($file, $configs[$field]);
        }

        return $results;
    }

    /**
     * Upload a single file
     * 
     * @param object $file CodeIgniter UploadedFile object
     * @param array $config Upload configuration
     * @return array Upload result with success status and filename or error
     */
    public function uploadSingleFile($file, array $config): array
    {
        // Validate file first
        $validationResult = $this->validateFile($file, $config);
        if (!$validationResult['isValid']) {
            return [
                'success' => false,
                'error' => $validationResult['error']
            ];
        }

        // Prepare upload directory
        $uploadDir = $this->prepareUploadDirectory($config['directory']);
        if (!$uploadDir['success']) {
            return [
                'success' => false,
                'error' => $uploadDir['error']
            ];
        }

        // Generate unique filename
        $newName = $this->generateUniqueFilename($file);

        // Move the file
        try {
            $file->move($uploadDir['path'], $newName);

            return [
                'success' => true,
                'filename' => $config['directory'] . '/' . $newName,
                'path' => $uploadDir['path'] . $newName
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate an uploaded file against configuration rules
     * 
     * @param object $file CodeIgniter UploadedFile object
     * @param array $config Configuration rules
     * @return array Validation result
     */
    protected function validateFile($file, array $config): array
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return [
                'isValid' => false,
                'error' => $file->getErrorString()
            ];
        }

        // Check file size
        if (isset($config['max_size']) && $file->getSize() > $config['max_size'] * 1024) {
            return [
                'isValid' => false,
                'error' => "File size exceeds maximum allowed ({$config['max_size']} KB)"
            ];
        }

        // Check file type
        if (isset($config['allowed_types']) && !in_array($file->getMimeType(), $config['allowed_types'])) {
            return [
                'isValid' => false,
                'error' => "File type {$file->getMimeType()} not allowed"
            ];
        }

        return [
            'isValid' => true,
            'error' => null
        ];
    }

    /**
     * Prepare the upload directory, creating it if it doesn't exist
     * 
     * @param string $dirPath Relative path from base upload directory
     * @return array Directory preparation result
     */
    protected function prepareUploadDirectory(string $dirPath): array
    {
        $fullPath = $this->baseUploadDir . $dirPath . '/';

        // Create directory if it doesn't exist
        if (!is_dir($fullPath)) {
            if (!mkdir($fullPath, 0777, true)) {
                return [
                    'success' => false,
                    'error' => "Failed to create directory: {$dirPath}",
                    'path' => null
                ];
            }
        }

        // Check if directory is writable
        if (!is_writable($fullPath)) {
            return [
                'success' => false,
                'error' => "Directory is not writable: {$dirPath}",
                'path' => null
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'path' => $fullPath
        ];
    }

    /**
     * Generate a unique filename for the uploaded file
     * 
     * @param object $file CodeIgniter UploadedFile object
     * @return string Generated filename
     */
    protected function generateUniqueFilename($file): string
    {
        $extension = $file->getExtension();
        if (empty($extension)) {
            $extension = pathinfo($file->getName(), PATHINFO_EXTENSION);
        }

        return uniqid('file_') . '_' . time() . '.' . $extension;
    }

    /**
     * Delete a file from the upload directory
     * 
     * @param string $filePath Relative path from base upload directory
     * @return bool True if file was deleted successfully
     */
    public function deleteFile(string $filePath): bool
    {
        $fullPath = $this->baseUploadDir . $filePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Get full server path for a stored file
     * 
     * @param string $filePath Relative path from base upload directory
     * @return string Full server path
     */
    public function getFilePath(string $filePath): string
    {
        return $this->baseUploadDir . $filePath;
    }
}
