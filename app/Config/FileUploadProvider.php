<?php

namespace App\Config;

use App\Services\FileUploadInterface;
use App\Services\FileUploadService;
use CodeIgniter\Config\BaseService;

/**
 * FileUploadProvider - Registers the file upload service in CI4's service container
 */
class FileUploadProvider extends BaseService
{
    /**
     * Register the FileUploadService as a singleton in the service container
     * 
     * @param bool $getShared Whether to return a shared instance
     * @return FileUploadInterface The file upload service
     */
    public static function fileUpload(bool $getShared = true): FileUploadInterface
    {
        if ($getShared) {
            return static::getSharedInstance('fileUpload');
        }

        return new FileUploadService();
    }
}
