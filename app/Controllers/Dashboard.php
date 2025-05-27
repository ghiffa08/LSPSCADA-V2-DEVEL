<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{

    public function index()
    {
        // Redirect assessors to their specific dashboard
        if (in_groups('Asesor')) {
            return redirect()->to('/asesor/dashboard');
        }

        $data = [
            'siteTitle' => 'Dashboard',
            'totalAdmin' => $this->dashboardModel->total_admin(),
            'totalAsesor' => $this->dashboardModel->total_asesor(),
            'totalAsesi' => $this->dashboardModel->total_asesi(),

        ];

        return view('dashboard', $data);
    }
}
