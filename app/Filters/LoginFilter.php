<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class LoginFilter implements FilterInterface
{
    /**
     * Verifies that a user is logged in, or redirects to login.
     *
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $authenticate = service('authentication');

        // OAuth routes should not be checked
        $reservedRoutes = [
            'login',
            'register',
            'forgot',
            'reset-password',
            'activate-account',
            'resend-activate',
            'auth/google',
            'OAuth/proses'
        ];

        // Make sure this isn't already an auth route
        foreach ($reservedRoutes as $reservedRoute) {
            if (strpos(current_url(), $reservedRoute) !== false) {
                return;
            }
        }

        // If no user is logged in then send them to the login form.
        if (!$authenticate->check()) {
            session()->set('redirect_url', current_url());
            return redirect()->to(site_url('login'));
        }
    }

    /**
     * @param array|null $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
