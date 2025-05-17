<?php

namespace App\Services;

/**
 * FileUploadInterface - Interface for file upload services
 * 
 * Defines required methods for any file upload service implementation,
 * allowing for easier testing and future implementation changes.
 */
interface FileUploadInterface
{
    /**
     * Upload multiple files at once
     * 
     * @param array $files Array of CodeIgniter file objects
     * @param array $configs Configuration for each file
     * @return array Results of each upload operation
     */
    public function uploadMultipleFiles(array $files, array $configs): array;

    /**
     * Upload a single file
     * 
     * @param object $file CodeIgniter UploadedFile object
     * @param array $config Upload configuration
     * @return array Upload result with success status and filename or error
     */
    public function uploadSingleFile($file, array $config): array;

    /**
     * Delete a file from the upload directory
     * 
     * @param string $filePath Relative path from base upload directory
     * @return bool True if file was deleted successfully
     */
    public function deleteFile(string $filePath): bool;

    /**
     * Get full server path for a stored file
     * 
     * @param string $filePath Relative path from base upload directory
     * @return string Full server path
     */
    public function getFilePath(string $filePath): string;
}
