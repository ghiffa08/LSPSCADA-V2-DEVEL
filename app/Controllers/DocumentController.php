<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentController extends BaseController
{
    public function index()
    {
        //
    }

    public function signatureShow($filename)
    {
        // Security check to prevent directory traversal attacks
        if (strpbrk($filename, "\\/?%*:|\"<>") !== false) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $path = WRITEPATH . 'uploads/user/signatures/' . $filename;

        if (!file_exists($path) || !is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Get file's mime type
        $mime = mime_content_type($path);

        // Only allow image files
        if (strpos($mime, 'image/') !== 0) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Set the appropriate headers
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: max-age=86400, public'); // Cache for one day

        // Output the file data
        readfile($path);
        exit;
    }
}
