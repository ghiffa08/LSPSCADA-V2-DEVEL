<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Services\Authentication\AuthenticationService;

class DashboardRouterController extends Controller
{
    protected AuthenticationService $authService;

    public function __construct()
    {
        $this->authService = new AuthenticationService();
    }
    public function index()
    {
        log_message('debug', '[DashboardRouter] Starting dashboard routing');

        // Check if user is authenticated
        if (!$this->authService->isAuthenticated()) {
            log_message('debug', '[DashboardRouter] User not authenticated, redirecting to login');
            return redirect()->to(site_url('login'));
        }

        // Get current user
        $user = $this->authService->getCurrentUser();
        if (!$user) {
            log_message('debug', '[DashboardRouter] Could not get current user, redirecting to login');
            return redirect()->to(site_url('login'));
        }

        log_message('debug', '[DashboardRouter] Got current user: ID=' . $user->id . ', Email=' . ($user->email ?? 'N/A'));

        // Ensure user is instance of our User entity
        if (!($user instanceof \App\Entities\User)) {
            $user = new \App\Entities\User((array)$user);
        }

        // Check role methods and redirect
        try {
            // Role-based redirection with priority order
            if ($user->isAdmin()) {
                log_message('debug', '[DashboardRouter] Redirecting admin to admin/dashboard');
                return redirect()->to(site_url('admin/dashboard'));
            }
            if ($user->isAsesor()) {
                log_message('debug', '[DashboardRouter] Redirecting asesor to asesor/dashboard');
                return redirect()->to(site_url('asesor/dashboard'));
            }
            if ($user->isAsesi()) {
                log_message('debug', '[DashboardRouter] Redirecting asesi to asesi/dashboard');
                return redirect()->to(site_url('asesi/dashboard'));
            }

            log_message('debug', '[DashboardRouter] No specific role found, redirecting to home');
            // If no specific role matches, redirect to home
            return redirect()->to(site_url('/'));
        } catch (\Exception $e) {
            log_message('error', '[DashboardRouter] Error during role checking: ' . $e->getMessage());
            return redirect()->to(site_url('login'));
        }
    }
}
