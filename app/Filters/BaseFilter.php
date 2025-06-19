<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

abstract class BaseFilter implements FilterInterface
{
    protected $authenticate;
    protected $authorize;
    protected $reservedRoutes;
    protected $landingRoute;
    public function __construct()
    {
        // Load auth helper for user() function
        helper('auth');

        // Initialize Auth components
        $this->authenticate = service('authentication');
        $this->authorize = service('authorization');

        // Set default routes
        $this->reservedRoutes = [
            'login' => route_to('login'),
            'register' => route_to('register'),
        ];

        $this->landingRoute = 'dashboard';
    }

    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    abstract public function before(RequestInterface $request, $arguments = null);

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Empty by default
    }

    /**
     * Check if user has required role
     */
    protected function hasRole(string $role): bool
    {
        if (!$this->authenticate->check()) {
            return false;
        }

        return $this->authorize->inGroup($role, $this->authenticate->id());
    }

    /**
     * Check if user has any of the required roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        if (!$this->authenticate->check()) {
            return false;
        }

        foreach ($roles as $role) {
            if ($this->authorize->inGroup($role, $this->authenticate->id())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current user data
     */
    protected function getCurrentUser()
    {
        if (!$this->authenticate->check()) {
            return null;
        }

        return user();
    }

    /**
     * Log security event
     */
    protected function logSecurityEvent(string $event, array $data = []): void
    {
        $user = $this->getCurrentUser();
        $logData = [
            'event' => $event,
            'user_id' => $user ? $user->id : null,
            'ip_address' => service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent(),
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $data
        ];

        log_message('info', 'Security Event: ' . json_encode($logData));
    }
}
