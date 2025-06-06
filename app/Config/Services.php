<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Libraries\Sidebar;
use App\Services\FileUploadInterface;
use App\Services\FileUploadService;
use App\Services\FeedbackService;
use App\Services\QRCodeService;
use App\Services\PDFService;
use CodeIgniter\Cache\CacheFactory;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    /**
     * The cache class provides a consistent interface to various
     * caching engines. This enhanced version implements multi-level
     * caching for dropdown data.
     *
     * @param mixed|null  $config  Cache configuration to use
     * @param bool        $getShared Whether to return a shared instance
     *
     * @return \CodeIgniter\Cache\CacheInterface
     */
    public static function cache($config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('cache', $config);
        }

        // If no config was supplied, use the default config
        if (empty($config)) {
            $config = new \Config\Cache();
        }

        // Enhanced cache configuration for location dropdowns
        // You can adjust these settings based on your specific needs
        if (is_string($config)) {
            $config = new \Config\Cache();
            $config->handler = $config;
        }

        // For location dropdowns, adjust cache settings
        if (
            isset($_SERVER['REQUEST_URI']) &&
            (strpos($_SERVER['REQUEST_URI'], 'api/kabupaten') !== false ||
                strpos($_SERVER['REQUEST_URI'], 'api/kecamatan') !== false ||
                strpos($_SERVER['REQUEST_URI'], 'api/desa') !== false)
        ) {
            // Use faster cache handler for location data
            // Options: file, redis, memcached, etc.
            $config->handler = 'file'; // Or 'redis' if available
            $config->path    = WRITEPATH . 'cache/location_data';

            // Ensure cache directory exists
            if (!is_dir($config->path)) {
                mkdir($config->path, 0777, true);
            }
        }

        $cacheFactory = new CacheFactory();
        return $cacheFactory->getHandler($config);
    }

    /**
     * Sidebar service
     *
     * @param bool $getShared Whether to return a shared instance
     * @return Sidebar
     */
    public static function sidebar(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('sidebar');
        }

        return new Sidebar();
    }

    /**
     * Return the feedback service
     *
     * @param bool $getShared
     * @return FeedbackService
     */
    public static function feedback(bool $getShared = true): FeedbackService
    {
        if ($getShared) {
            return static::getSharedInstance('feedback');
        }

        return new FeedbackService();
    }

    /**
     * Return the QR code service
     *
     * @param bool $getShared
     * @return QRCodeService
     */
    public static function qrcode(bool $getShared = true): QRCodeService
    {
        if ($getShared) {
            return static::getSharedInstance('qrcode');
        }

        return new QRCodeService();
    }

    /**
     * Return the PDF service
     *
     * @param bool $getShared
     * @return PDFService
     */
    public static function pdf(bool $getShared = true): PDFService
    {
        if ($getShared) {
            return static::getSharedInstance('pdf');
        }

        return new PDFService();
    }

    /**
     * Return file upload service
     *
     * @param bool $getShared
     * @return FileUploadInterface
     */
    public static function fileUpload(bool $getShared = true): FileUploadInterface
    {
        if ($getShared) {
            return static::getSharedInstance('fileUpload');
        }

        return new FileUploadService();
    }

    /**
     * AsesiService factory
     */
    public static function AsesiService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('AsesiService');
        }
        $repository = new \App\Repositories\AsesiRepository();
        $db = \Config\Database::connect();
        return new \App\Services\AsesiService($repository, $db);
    }

    /**
     * ValidationService factory
     */
    public static function ValidationService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('ValidationService');
        }
        return new \App\Services\ValidationService();
    }

    /**
     * CustomResponseService factory
     */
    public static function CustomResponseService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('CustomResponseService');
        }
        return new \App\Services\CustomResponseService();
    }
}
