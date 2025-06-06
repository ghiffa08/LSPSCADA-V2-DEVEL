<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DashboardRouterController extends Controller
{
    public function index()
    {
        $user = user();
        if (!$user) {
            return redirect()->to(site_url('login'));
        }
        if (!($user instanceof \App\Entities\User)) {
            $user = new \App\Entities\User((array)$user);
        }
        if ($user->isAdmin()) {
            return redirect()->to(site_url('admin/dashboard'));
        }
        if ($user->isAsesor()) {
            return redirect()->to(site_url('asesor/dashboard'));
        }
        if ($user->isAsesi()) {
            return redirect()->to(site_url('asesi/dashboard'));
        }
        // Default fallback
        return redirect()->to(site_url('/'));
    }
}
