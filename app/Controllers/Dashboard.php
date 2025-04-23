<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{

    public function index()
    {

        $data = [
            'siteTitle' => 'Dashboard',
            'totalAdmin' => $this->dashboard->total_admin(),
            'totalAsesor' => $this->dashboard->total_asesor(),
            'totalAsesi' => $this->dashboard->total_asesi(),

        ];

        return view('dashboard', $data);
    }
}
