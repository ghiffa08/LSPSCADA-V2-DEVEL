<?php

/**
 * Auth Helper Functions
 * 
 * Collection of helper functions for Myth/Auth authentication
 */

if (!function_exists('user')) {
    /**
     * Get the currently logged in user entity
     *
     * @return \App\Entities\User|null
     */
    function user()
    {
        // Cek session yang sudah kita set di AuthenticationService
        $sessionUserId = session('logged_in');
        if ($sessionUserId) {
            $userModel = new \App\Models\UserMythModel();
            $user = $userModel->find($sessionUserId);
            if ($user) {
                return $user;
            }
        }

        // Fallback ke Myth/Auth
        $auth = service('authentication');
        if (!$auth->check()) {
            return null;
        }

        $user = $auth->user();

        // Ensure we return an App\Entities\User instance
        if ($user && !($user instanceof \App\Entities\User)) {
            // Convert to our User entity if it's not already
            $userModel = new \App\Models\UserMythModel();
            $user = $userModel->find($user->id);
        }

        return $user;
    }
}

if (!function_exists('user_id')) {
    /**
     * Get the currently logged in user ID
     *
     * @return int|null
     */
    function user_id()
    {
        $user = user();
        return $user ? $user->id : null;
    }
}

if (!function_exists('logged_in')) {
    /**
     * Check if a user is logged in
     *
     * @return bool
     */
    function logged_in()
    {
        // Cek session yang sudah kita set di AuthenticationService
        $sessionUserId = session('logged_in');
        if ($sessionUserId) {
            return true;
        }

        // Fallback ke Myth/Auth
        return service('authentication')->check();
    }
}

if (!function_exists('has_permission')) {
    /**
     * Check if the logged in user has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    function has_permission(string $permission)
    {
        $auth = service('authorization');
        return $auth->hasPermission($permission, user_id());
    }
}

if (!function_exists('in_groups')) {
    /**
     * Check if the logged in user is in any of the given groups
     *
     * @param string|array $groups
     * @return bool
     */
    function in_groups($groups)
    {
        // Cek session roles yang sudah kita set
        $sessionRoles = session('roles');
        if ($sessionRoles) {
            if (is_string($groups)) {
                $groups = [$groups];
            }
            foreach ($groups as $group) {
                if (in_array(strtolower($group), array_map('strtolower', $sessionRoles))) {
                    return true;
                }
            }
        }

        // Fallback ke Myth/Auth
        $auth = service('authorization');

        if (is_string($groups)) {
            $groups = [$groups];
        }

        return $auth->inGroup($groups, user_id());
    }
}

// Enhanced Authentication Helper Functions using AuthenticationService

if (!function_exists('authService')) {
    /**
     * Get AuthenticationService instance
     */
    function authService(): \App\Services\Authentication\AuthenticationService
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new \App\Services\Authentication\AuthenticationService();
        }
        return $instance;
    }
}

if (!function_exists('currentUser')) {
    /**
     * Get current authenticated user using AuthenticationService
     */
    function currentUser(): ?\App\Entities\User
    {
        return authService()->getCurrentUser();
    }
}

if (!function_exists('isLoggedIn')) {
    /**
     * Check if user is logged in using AuthenticationService
     */
    function isLoggedIn(): bool
    {
        return authService()->isAuthenticated();
    }
}

if (!function_exists('hasRole')) {
    /**
     * Check if current user has specific role
     */
    function hasRole(string $role): bool
    {
        $user = currentUser();
        return $user ? $user->hasRole($role) : false;
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Check if current user is admin
     */
    function isAdmin(): bool
    {
        $user = currentUser();
        return $user ? $user->isAdmin() : false;
    }
}

if (!function_exists('isAsesor')) {
    /**
     * Check if current user is asesor
     */
    function isAsesor(): bool
    {
        $user = currentUser();
        return $user ? $user->isAsesor() : false;
    }
}

if (!function_exists('isAsesi')) {
    /**
     * Check if current user is asesi
     */
    function isAsesi(): bool
    {
        $user = currentUser();
        return $user ? $user->isAsesi() : false;
    }
}

if (!function_exists('requireAuth')) {
    /**
     * Require authentication - redirect to login if not authenticated
     */
    function requireAuth(): void
    {
        if (!isLoggedIn()) {
            session()->set('redirect_url', current_url());
            redirect()->to(site_url('login'))->send();
            exit;
        }
    }
}

if (!function_exists('requireRole')) {
    /**
     * Require specific role - show 403 if user doesn't have role
     */
    function requireRole(string $role): void
    {
        requireAuth();

        if (!hasRole($role)) {
            throw new \Myth\Auth\Exceptions\PermissionException('Insufficient permissions');
        }
    }
}

if (!function_exists('redirectByRole')) {
    /**
     * Redirect user based on their role
     */
    function redirectByRole(): \CodeIgniter\HTTP\RedirectResponse
    {
        $user = currentUser();
        if (!$user) {
            return redirect()->to(site_url('login'));
        }

        if ($user->isAdmin()) {
            return redirect()->to(site_url('admin/dashboard'));
        } elseif ($user->isAsesor()) {
            return redirect()->to(site_url('asesor/dashboard'));
        } elseif ($user->isAsesi()) {
            return redirect()->to(site_url('asesi/dashboard'));
        }

        return redirect()->to(site_url('dashboard'));
    }
}
