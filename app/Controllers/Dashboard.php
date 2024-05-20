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
            'listSkema' => $this->skema->AllSkema(),
            'listAPL1' => $this->apl1->getUnvalidatedData(),
            'listAPL1Sucess' => $this->apl1->getValidatedData(),
        ];

        return view('dashboard', $data);
    }
}
