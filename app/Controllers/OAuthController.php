<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Google_Client;

class OAuthController extends BaseController
{

    protected $googleClient;
    public function __construct()
    {
        $this->googleClient = new Google_Client();

        $this->googleClient->setClientId('19797765617-b50c3roaioqka6jf3q3jr6796a4l2sla.apps.googleusercontent.com');
        $this->googleClient->setClientSecret('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');
        $this->googleClient->setRedirectUri('http://localhost:8080/OAuth/proses');
        $this->googleClient->addScope('email');
        $this->googleClient->addScope('profile');
    }
    public function index()
    {
        //
    }

    public function proses()
    {
    }
}
