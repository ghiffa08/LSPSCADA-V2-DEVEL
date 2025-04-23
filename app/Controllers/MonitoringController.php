<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class MonitoringController extends BaseController
{
    public function index()
    {
        $data = [
            'siteTitle' => 'Monitoring Peserta',
            'listMonitoring' => $this->apl1->getMonitoring()
        ];

        // dd($data);
        return view('dashboard/monitoring', $data);
    }
}
