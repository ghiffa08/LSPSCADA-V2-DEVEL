<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TUKController extends BaseController
{
    public function index()
    {
        $data = [
            'listTUK' => $this->tukModel->findAll(),
            'siteTitle' => 'Kelola TUK'
        ];

        return view('admin/kelola_tuk', $data);
    }
}
