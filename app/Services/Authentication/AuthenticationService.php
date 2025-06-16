<?php

namespace App\Services\Authentication;

use App\DTOs\Authentication\LoginRequest;
use App\DTOs\Authentication\RegisterRequest;
use App\DTOs\Authentication\AuthResponse;
use App\DTOs\Authentication\PasswordResetRequest;
use App\Entities\User;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use App\Services\EmailService;
use App\Services\ValidationService;
use Myth\Auth\Models\UserModel;
use Myth\Auth\Authentication\LocalAuthenticator;
use CodeIgniter\I18n\Time;
use Exception;

/**
 * Enhanced Authentication Service
 * 
 * Implements comprehensive authentication operations with clean architecture
 * Provides secure, role-based authentication with proper error handling
 * Supports both manual and OAuth authentication
 */
class AuthenticationService
{
    protected UserModel $userModel;
    protected UserMythModel $userMythModel;
    protected GroupUserModel $groupUserModel;
    protected LocalAuthenticator $authenticator;
    protected ValidationService $validationService;
    protected EmailService $emailService;
    protected $config;
    protected $session;
    protected $db;
    public function __construct()
    {
        $this->userModel = model(UserModel::class);
        $this->userMythModel = new UserMythModel();
        $this->groupUserModel = new GroupUserModel();

        // Initialize the authenticator with proper config and models
        $this->authenticator = new \Myth\Auth\Authentication\LocalAuthenticator(config('Auth'));

        // Set the required models to prevent "User model must be loaded prior to use" error
        $this->authenticator->setUserModel($this->userMythModel);
        $this->authenticator->setLoginModel(model(\Myth\Auth\Models\LoginModel::class));

        $this->validationService = new ValidationService();
        $this->emailService = new \App\Services\EmailService();
        $this->config = config('Auth');
        $this->session = service('session');
        $this->db = \Config\Database::connect();
    }

    /**
     * Authenticate user with credentials
     */
    public function login(LoginRequest $request): AuthResponse
    {
        try {
            // Validate request
            $errors = $request->validate();
            if (!empty($errors)) {
                return AuthResponse::validationError($errors);
            }

            // Rate limiting check
            $throttler = service('throttler');
            $throttleKey = md5($request->ipAddress . '_' . $request->login);

            if ($throttler->check($throttleKey, 5, MINUTE) === false) {
                return AuthResponse::rateLimited(
                    'Too many login attempts. Please try again in ' . $throttler->getTokentime() . ' seconds.',
                    (int)$throttler->getTokentime()
                );
            }            // Ensure session is properly initialized for manual login
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            // Attempt authentication with error handling
            $credentials = [
                $request->getLoginType() => $request->login,
                'password' => $request->password
            ];

            try {
                $authResult = $this->authenticator->attempt($credentials, $request->remember);
            } catch (Exception $e) {
                log_message('error', 'Authentication attempt failed: ' . $e->getMessage());
                return AuthResponse::error('Authentication error occurred. Please try again.');
            }

            if (!$authResult) {
                // Log failed attempt
                $this->logActivity(
                    'login_attempt',
                    null,
                    $request->login,
                    ['login_type' => $request->getLoginType()],
                    $request->ipAddress,
                    $request->userAgent,
                    false
                );

                return AuthResponse::error(
                    'Invalid credentials provided',
                    ['login' => 'The provided credentials are incorrect'],
                    401
                );
            } // Get authenticated user
            $user = $this->authenticator->user();

            // Ensure user is instance of our User entity
            if (!($user instanceof User)) {
                $user = new User((array)$user);
            }

            // Validate user ID
            if (empty($user->id)) {
                log_message('error', 'Login: Authenticated user has no ID');
                $this->authenticator->logout(); // Clean up invalid session
                return AuthResponse::error('Authentication failed: Invalid user data');
            }

            // Ensure ID is integer
            $user->id = (int)$user->id;            // Abaikan force_pass_reset jika user OAuth
            $isOAuthUser = !empty($user->google_id) || !empty($user->facebook_id) || !empty($user->oauth_provider);

            // Log untuk debugging
            log_message('debug', '[Login] User ID: ' . $user->id . ', force_pass_reset: ' . ($user->force_pass_reset ? 'true' : 'false') . ', isOAuthUser: ' . ($isOAuthUser ? 'true' : 'false') . ', google_id: ' . ($user->google_id ?? 'NULL'));

            if ($user->force_pass_reset === true && !$isOAuthUser) {
                log_message('info', '[Login] Redirecting to reset password for user: ' . $user->id);
                return AuthResponse::success(
                    'Password reset required',
                    $user
                )->withRedirect(site_url('reset-password?token=' . ($user->reset_hash ?? '')))
                    ->withData('force_password_reset', true);
            } // Log successful attempt
            $this->logActivity(
                'login',
                $user->id,
                $user->email ?? $request->login,
                [
                    'login_type' => $request->getLoginType(),
                    'remember' => $request->remember,
                    'redirect_url' => $this->determineRedirectUrl($user)
                ],
                $request->ipAddress,
                $request->userAgent,
                true
            );

            // Setelah login sukses, set session user secara eksplisit
            session()->set('logged_in', $user->id);
            session()->set('user_email', $user->email);
            session()->set('roles', model('GroupUserModel')->getRolesByUserId($user->id));
            log_message('debug', 'Session after login: ' . print_r(session()->get(), true));

            // Determine redirect URL based on role
            $redirectUrl = $this->determineRedirectUrl($user);

            return AuthResponse::success(
                'Login successful',
                $user
            )->withRedirect($redirectUrl)
                ->withData('login_time', Time::now()->toDateTimeString());
        } catch (Exception $e) {
            log_message('error', 'Authentication error: ' . $e->getMessage());
            return AuthResponse::error('An authentication error occurred');
        }
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): AuthResponse
    {
        try {
            // Validate request
            $errors = $request->validate();
            if (!empty($errors)) {
                return AuthResponse::validationError($errors);
            }
            // Check if registration is allowed
            $allowRegistration = $this->getConfigProperty('allowRegistration', true);
            if (!$allowRegistration) {
                return AuthResponse::forbidden('User registration is currently disabled');
            }

            // Check for existing user
            if ($this->userModel->where('email', $request->email)->first()) {
                return AuthResponse::validationError(['email' => 'This email is already registered']);
            }
            if ($this->userModel->where('username', $request->username)->first()) {
                return AuthResponse::validationError(['username' => 'This username is already taken']);
            }
            // Create user entity, only use valid users fields
            $userData = $request->toUserArray();
            $user = new User($userData);
            $isOAuthUser = !empty($request->googleId);
            $requireActivation = $this->getConfigProperty('requireActivation');

            if ($requireActivation === null || $isOAuthUser) {
                // Activate immediately if activation not required OR if this is an OAuth user
                $user->activate();
            } else {
                $user->generateActivateHash();
            }

            // Set group before save to ensure user masuk ke role yang benar
            $this->userMythModel->withGroup($request->getGroup());
            if (!$this->userMythModel->save($user)) {
                return AuthResponse::error(
                    'Failed to create user account',
                    $this->userMythModel->errors(),
                    500
                );
            }
            // Get the inserted user ID if not set on the entity
            $userId = $user->id ?? $this->userMythModel->getInsertID();
            if (!$userId || $userId <= 0) {
                return AuthResponse::error('Failed to get user ID after registration');
            }
            $user->id = $userId;

            // Setelah save, cek role user, jika kosong assign ke group asesi dan log error
            $roles = model('GroupUserModel')->getRolesByUserId($user->id);
            if (empty($roles)) {
                log_message('error', 'User baru ID ' . $user->id . ' tidak punya role, assign ke asesi');
                $this->userMythModel->withGroup('asesi');
                $this->userMythModel->save($user);
            }
            // Refresh session role user jika sudah login
            if (service('authentication')->check()) {
                session()->set('roles', model('GroupUserModel')->getRolesByUserId($user->id));
            }

            return AuthResponse::success(
                'Registration successful. You may now log in.',
                $user
            )->withRedirect(site_url('login'));
        } catch (Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return AuthResponse::error('A registration error occurred');
        }
    }

    /**
     * Log out the current user
     */    public function logout(): AuthResponse
    {
        try {
            if ($this->authenticator->check()) {
                $user = $this->authenticator->user();
                $userId = $user->id ?? null;
                $userEmail = $user->email ?? null;

                $this->authenticator->logout();

                // Log logout activity
                $this->logActivity(
                    'logout',
                    $userId,
                    $userEmail,
                    ['logout_time' => Time::now()->toDateTimeString()]
                );

                return AuthResponse::success(
                    'Logout successful'
                )->withRedirect(site_url('/'))
                    ->withData('logout_time', Time::now()->toDateTimeString());
            }

            return AuthResponse::error('No active session to logout');
        } catch (Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage());
            return AuthResponse::error('A logout error occurred');
        }
    }

    /**
     * Initiate password reset process
     */    public function forgotPassword(string $email): AuthResponse
    {
        try {
            $activeResetter = $this->getConfigProperty('activeResetter');
            if ($activeResetter === null) {
                return AuthResponse::forbidden('Password reset is currently disabled');
            }

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return AuthResponse::validationError(['email' => 'Please provide a valid email address']);
            }            // Find user
            $user = $this->userModel->where('email', $email)->first();
            if (!$user) {
                // Log failed password reset attempt
                $this->logActivity(
                    'password_reset_request',
                    null,
                    $email,
                    ['reason' => 'User not found'],
                    null,
                    null,
                    false
                );

                // Return success even if user not found (security)
                return AuthResponse::success(
                    'If an account with that email exists, password reset instructions have been sent.'
                )->withRedirect(site_url('login'));
            }

            // Generate reset hash
            $user->generateResetHash();
            $this->userModel->save($user);

            // Log password reset request
            $this->logActivity(
                'password_reset_request',
                $user->id,
                $email,
                ['token' => $user->reset_hash]
            );

            // Send reset email
            $resetter = service('resetter');
            $sent = $resetter->send($user);

            if (!$sent) {
                return AuthResponse::error(
                    'Failed to send password reset email',
                    ['email' => $resetter->error() ?? 'Unknown error']
                );
            }

            return AuthResponse::success(
                'Password reset instructions have been sent to your email.'
            )->withRedirect(site_url('login'));
        } catch (Exception $e) {
            log_message('error', 'Password reset error: ' . $e->getMessage());
            return AuthResponse::error('A password reset error occurred');
        }
    }

    /**
     * Reset password with token
     */    public function resetPassword(PasswordResetRequest $request): AuthResponse
    {
        try {
            $activeResetter = $this->getConfigProperty('activeResetter');
            if ($activeResetter === null) {
                return AuthResponse::forbidden('Password reset is currently disabled');
            }

            // Validate request
            $errors = $request->validate();
            if (!empty($errors)) {
                return AuthResponse::validationError($errors);
            }

            // Find user with valid token
            $user = $this->userModel
                ->where('email', $request->email)
                ->where('reset_hash', $request->token)
                ->first();

            if (!$user) {
                return AuthResponse::error(
                    'Invalid reset token or email',
                    ['token' => 'The reset token is invalid or has expired']
                );
            }

            // Check token expiration
            if (!empty($user->reset_expires) && time() > $user->reset_expires->getTimestamp()) {
                return AuthResponse::error(
                    'Reset token has expired',
                    ['token' => 'The reset token has expired. Please request a new one.']
                );
            }

            // Update password
            $user->password = $request->password;
            $user->reset_hash = null;
            $user->reset_at = Time::now();
            $user->reset_expires = null;
            $user->force_pass_reset = false;

            if (!$this->userModel->save($user)) {
                return AuthResponse::error('Failed to update password');
            }

            return AuthResponse::success(
                'Password has been successfully reset. You may now log in with your new password.'
            )->withRedirect(site_url('login'));
        } catch (Exception $e) {
            log_message('error', 'Password reset error: ' . $e->getMessage());
            return AuthResponse::error('A password reset error occurred');
        }
    }

    /**
     * Activate user account
     */
    public function activateAccount(string $token): AuthResponse
    {
        try {
            if (empty($token)) {
                return AuthResponse::validationError(['token' => 'Activation token is required']);
            }

            // Rate limiting
            $throttler = service('throttler');
            if ($throttler->check(md5(service('request')->getIPAddress()), 2, MINUTE) === false) {
                return AuthResponse::rateLimited('Too many activation attempts');
            }            // Find user
            $user = $this->userModel
                ->where('activate_hash', $token)
                ->where('active', 0)
                ->first();

            if (!$user) {
                // Log failed activation attempt
                $this->logActivity(
                    'activation_attempt',
                    null,
                    null,
                    ['token' => $token, 'reason' => 'Invalid or expired token'],
                    null,
                    null,
                    false
                );

                return AuthResponse::error(
                    'Invalid activation token',
                    ['token' => 'The activation token is invalid or the account is already active']
                );
            }

            // Log successful activation attempt
            $this->logActivity(
                'activation_attempt',
                $user->id,
                $user->email,
                ['token' => $token]
            );

            // Activate user
            $user->activate();
            if (!$this->userModel->save($user)) {
                return AuthResponse::error('Failed to activate account');
            }

            return AuthResponse::success(
                'Account successfully activated. You may now log in.'
            )->withRedirect(site_url('login'));
        } catch (Exception $e) {
            log_message('error', 'Account activation error: ' . $e->getMessage());
            return AuthResponse::error('An activation error occurred');
        }
    }

    /**
     * Resend activation email
     */    public function resendActivation(string $email): AuthResponse
    {
        try {
            $requireActivation = $this->getConfigProperty('requireActivation');
            if ($requireActivation === null) {
                return AuthResponse::forbidden('Account activation is not required');
            }

            // Rate limiting
            $throttler = service('throttler');
            $throttleKey = md5(service('request')->getIPAddress() . '_resend');
            if ($throttler->check($throttleKey, 2, MINUTE) === false) {
                return AuthResponse::rateLimited('Too many resend attempts');
            }

            // Find inactive user
            $user = $this->userModel
                ->where('email', $email)
                ->where('active', 0)
                ->first();

            if (!$user) {
                // Return success for security (don't reveal if email exists)
                return AuthResponse::success(
                    'If an inactive account with that email exists, activation instructions have been sent.'
                );
            }

            // Send activation email
            $activator = service('activator');
            $sent = $activator->send($user);

            if (!$sent) {
                return AuthResponse::error(
                    'Failed to send activation email',
                    ['email' => $activator->error() ?? 'Unknown error']
                );
            }

            return AuthResponse::success(
                'Activation instructions have been sent to your email.'
            );
        } catch (Exception $e) {
            log_message('error', 'Resend activation error: ' . $e->getMessage());
            return AuthResponse::error('An activation error occurred');
        }
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        if (!$this->authenticator->check()) {
            return null;
        }

        $user = $this->authenticator->user();

        // Ensure it's our User entity
        if (!($user instanceof User)) {
            $user = new User((array)$user);
        }

        return $user;
    }
    /**
     * Check if user is currently authenticated
     */    public function isAuthenticated(): bool
    {
        // Check session first (our enhanced auth)
        $sessionUserId = session('logged_in');
        if ($sessionUserId) {
            return true;
        }

        // Fallback to Myth/Auth
        return $this->authenticator->check();
    }
    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        // Check session first (our enhanced auth)
        $sessionUserId = session('logged_in');
        if ($sessionUserId) {
            try {
                log_message('debug', '[getCurrentUser] Looking for user ID: ' . $sessionUserId);
                $user = $this->userModel->find($sessionUserId);
                if ($user) {
                    log_message('debug', '[getCurrentUser] Found user from DB: ID=' . ($user->id ?? 'N/A') . ', Email=' . ($user->email ?? 'N/A'));

                    // Ensure user is instance of our User entity
                    if (!($user instanceof User)) {
                        $user = new User((array)$user);
                        log_message('debug', '[getCurrentUser] Converted to App\Entities\User');
                    }

                    // Ensure ID is properly set
                    if (empty($user->id)) {
                        $user->id = $sessionUserId;
                        log_message('debug', '[getCurrentUser] Set user ID from session: ' . $sessionUserId);
                    }

                    return $user;
                } else {
                    log_message('warning', '[getCurrentUser] User not found in DB for ID: ' . $sessionUserId);
                }
            } catch (Exception $e) {
                log_message('error', 'getCurrentUser: Error loading user from session: ' . $e->getMessage());
            }
        }

        // Fallback to Myth/Auth
        if (!$this->authenticator->check()) {
            return null;
        }

        $user = $this->authenticator->user();

        // Ensure user is instance of our User entity
        if (!($user instanceof User)) {
            $user = new User((array)$user);
        }

        // Validate user ID
        if (empty($user->id)) {
            log_message('error', 'getCurrentUser: User entity has no ID');
            return null;
        }

        // Ensure ID is integer
        $user->id = (int)$user->id;

        return $user;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        $user = $this->getAuthenticatedUser();
        return $user ? $user->hasRole($role) : false;
    }

    /**
     * Get user's roles
     */
    public function getUserRoles(): array
    {
        $user = $this->getAuthenticatedUser();
        return $user ? ($user->getRoles() ?? []) : [];
    }

    /**
     * Refresh user session
     */
    public function refreshSession(): bool
    {
        try {
            if (!$this->isAuthenticated()) {
                return false;
            }

            $user = $this->getAuthenticatedUser();
            if (!$user) {
                return false;
            }            // Refresh user data from database
            $freshUser = $this->userMythModel->find($user->id);
            if (!$freshUser) {
                return false;
            }

            // Update session with fresh user data
            $this->session->set('user', $freshUser);
            return true;
        } catch (Exception $e) {
            log_message('error', 'Session refresh error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get authentication attempt logs
     */
    public function getAuthenticationLogs(?string $email = null, int $limit = 10): array
    {
        try {
            $builder = $this->userModel->db->table('auth_logins');

            if ($email) {
                $builder->where('email', $email);
            }

            return $builder->orderBy('date', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
        } catch (Exception $e) {
            log_message('error', 'Error fetching auth logs: ' . $e->getMessage());
            return [];
        }
    }
    /**
     * Determine redirect URL based on user role
     */
    public function determineRedirectUrl(User $user): string
    {
        try {
            log_message('debug', '[RedirectURL] Determining redirect for user: ' . $user->id . ', email: ' . $user->email);

            // Check for stored redirect URL first - only if it's not an auth-related URL
            $redirectUrl = session('redirect_url');
            if ($redirectUrl && !$this->isAuthRelatedUrl($redirectUrl)) {
                log_message('debug', '[RedirectURL] Using stored redirect URL: ' . $redirectUrl);
                session()->remove('redirect_url');
                return $redirectUrl;
            }

            // Remove any auth-related redirect URL
            if ($redirectUrl) {
                session()->remove('redirect_url');
                log_message('debug', '[RedirectURL] Removed auth-related redirect URL: ' . $redirectUrl);
            }

            // For OAuth users, ensure we don't redirect to password reset
            $isOAuthUser = !empty($user->google_id) || !empty($user->facebook_id);
            if ($isOAuthUser) {
                log_message('debug', '[RedirectURL] OAuth user detected, using role-based redirect');
            }

            // Role-based redirection with priority order and logging
            if ($user->isAdmin()) {
                $finalUrl = site_url('admin/dashboard');
                log_message('debug', '[RedirectURL] Admin user, redirecting to: ' . $finalUrl);
                return $finalUrl;
            } elseif ($user->isAsesor()) {
                $finalUrl = site_url('asesor/dashboard');
                log_message('debug', '[RedirectURL] Asesor user, redirecting to: ' . $finalUrl);
                return $finalUrl;
            } elseif ($user->isAsesi()) {
                $finalUrl = site_url('asesi/dashboard');
                log_message('debug', '[RedirectURL] Asesi user, redirecting to: ' . $finalUrl);
                return $finalUrl;
            }

            // Default fallback
            $fallbackUrl = site_url('dashboard');
            log_message('debug', '[RedirectURL] Using fallback redirect: ' . $fallbackUrl);
            return $fallbackUrl;
        } catch (Exception $e) {
            log_message('error', '[RedirectURL] Error determining redirect URL: ' . $e->getMessage());
            $errorFallback = site_url('dashboard');
            log_message('debug', '[RedirectURL] Using error fallback: ' . $errorFallback);
            return $errorFallback;
        }
    }

    /**
     * Check if URL is auth-related
     */
    private function isAuthRelatedUrl(string $url): bool
    {
        $authUrls = [
            'login',
            'register',
            'logout',
            'forgot',
            'reset-password',
            'activate-account',
            'resend-activate',
            'auth/google',
            'OAuth/proses'
        ];

        foreach ($authUrls as $authUrl) {
            if (strpos($url, $authUrl) !== false) {
                return true;
            }
        }

        return false;
    }
    /**
     * Create role-specific data for new users
     */
    protected function createRoleSpecificData(int $userId, string $role, array $additionalData = []): void
    {
        // Validate userId
        if (empty($userId) || $userId <= 0) {
            log_message('error', 'createRoleSpecificData called with invalid userId: ' . var_export($userId, true));
            throw new \InvalidArgumentException('Invalid user ID provided to createRoleSpecificData');
        }

        try {
            $db = \Config\Database::connect();

            switch (strtolower($role)) {
                case 'asesi':
                    // Get available columns in asesi table to avoid field errors
                    $asesiFields = $db->getFieldNames('asesi');

                    // Define default data for asesi with only existing columns
                    $defaultAsesiData = [
                        'id_user' => $userId,
                        'created_at' => Time::now(),
                        'updated_at' => Time::now()
                    ];
                    // Add optional fields only if they exist in the table
                    $optionalFields = [
                        // 'nik' => '',
                        // 'tempat_lahir' => '',
                        // // 'tanggal_lahir' => null, // DIHAPUS agar tidak error jika tidak ada di tabel
                        // 'jenis_kelamin' => null,
                        // 'pendidikan_terakhir' => null,
                    ];

                    // Add no_hp if it exists in the table
                    if (in_array('no_hp', $asesiFields)) {
                        $optionalFields['no_hp'] = null;
                    }

                    foreach ($optionalFields as $field => $defaultValue) {
                        if (in_array($field, $asesiFields)) {
                            $defaultAsesiData[$field] = $defaultValue;
                        }
                    }

                    $asesiData = array_merge($defaultAsesiData, $additionalData);

                    $db->table('asesi')->insert($asesiData);
                    break;
                case 'asesor':
                    // Get available columns in asesor table to avoid field errors
                    $asesorFields = $db->getFieldNames('asesor');

                    // Define default data for asesor with only existing columns
                    $defaultAsesorData = [
                        'id_user' => $userId,
                        'created_at' => Time::now(),
                        'updated_at' => Time::now()
                    ];

                    $asesorData = array_merge($defaultAsesorData, $additionalData);

                    $db->table('asesor')->insert($asesorData);
                    break;

                case 'admin':
                    // Admin users typically don't need additional tables
                    // but we can log the creation
                    log_message('info', "Admin user created with ID: {$userId}");
                    break;
            }
        } catch (Exception $e) {
            log_message('error', "Failed to create role-specific data for user {$userId}: " . $e->getMessage());
        }
    }
    /**
     * Log user activity using myth/auth tables and CodeIgniter logging
     * 
     * Uses auth_logins table for authentication-related events and 
     * CodeIgniter's log_message() for detailed activity tracking
     * 
     * @param string $activityType Type of activity (login, logout, password_reset, etc.)
     * @param int|null $userId User ID (if available)
     * @param string|null $email User email (for auth_logins table)
     * @param array $details Additional activity details
     * @param string $ipAddress IP address
     * @param string $userAgent User agent string
     * @param bool $success Whether the activity was successful
     */
    public function logActivity(
        string $activityType,
        ?int $userId = null,
        ?string $email = null,
        array $details = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
        bool $success = true
    ): void {
        try {
            // Get current request information if not provided
            $request = service('request');
            $ipAddress = $ipAddress ?? $request->getIPAddress();
            $userAgent = $userAgent ?? $request->getUserAgent();

            // For authentication-related activities, log to auth_logins table
            if (in_array($activityType, ['login', 'logout', 'login_attempt', 'oauth_login'])) {
                $this->db->table('auth_logins')->insert([
                    'ip_address' => $ipAddress,
                    'email' => $email ?? '',
                    'user_id' => $userId,
                    'date' => Time::now(),
                    'success' => $success ? 1 : 0
                ]);
            }

            // For password reset activities, log to auth_reset_attempts table
            if ($activityType === 'password_reset_request' && $email) {
                $this->userMythModel->logResetAttempt(
                    $email,
                    $details['token'] ?? null,
                    $ipAddress,
                    $userAgent
                );
            }

            // For activation activities, log to auth_activation_attempts table
            if ($activityType === 'activation_attempt') {
                $this->userMythModel->logActivationAttempt(
                    $details['token'] ?? null,
                    $ipAddress,
                    $userAgent
                );
            }

            // Always log detailed activity information using CodeIgniter's logging
            $logLevel = $success ? 'info' : 'warning';
            $logMessage = sprintf(
                'User Activity - Type: %s, User ID: %s, Email: %s, IP: %s, Success: %s, Details: %s',
                $activityType,
                $userId ?? 'N/A',
                $email ?? 'N/A',
                $ipAddress,
                $success ? 'Yes' : 'No',
                !empty($details) ? json_encode($details) : 'None'
            );

            log_message($logLevel, $logMessage);
        } catch (Exception $e) {
            // Always log errors, but don't throw exceptions to avoid breaking the main flow
            log_message('error', 'Failed to log user activity: ' . $e->getMessage());
        }
    }
    /**
     * Login user by ID (used for OAuth and other programmatic logins)
     */
    public function loginById(
        int $userId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        string $loginType = 'oauth'
    ): AuthResponse {
        try {
            // Find user
            $user = $this->userMythModel->find($userId);
            if (!$user) {
                $this->logActivity(
                    'oauth_login',
                    $userId,
                    null,
                    ['reason' => 'User not found', 'login_type' => $loginType],
                    $ipAddress,
                    $userAgent,
                    false
                );

                return AuthResponse::error('User not found', [], 404);
            }

            // Check if user is active
            if (!$user->isActive()) {
                $this->logActivity(
                    'oauth_login',
                    $userId,
                    $user->email,
                    ['reason' => 'User account inactive', 'login_type' => $loginType],
                    $ipAddress,
                    $userAgent,
                    false
                );

                return AuthResponse::forbidden('User account is inactive');
            }

            // Ensure user is instance of our User entity
            if (!($user instanceof User)) {
                $user = new User((array)$user);
            }

            // Login the user using Myth/Auth's standard login method
            $loginSuccess = $this->authenticator->login($user, false);

            if (!$loginSuccess) {
                $this->logActivity(
                    'oauth_login',
                    $user->id,
                    $user->email,
                    ['reason' => 'Login failed', 'login_type' => $loginType, 'error' => $this->authenticator->error()],
                    $ipAddress,
                    $userAgent,
                    false
                );

                return AuthResponse::error('Login failed: ' . ($this->authenticator->error() ?? 'Unknown error'));
            }

            // Log successful OAuth login
            $this->logActivity(
                'oauth_login',
                $user->id,
                $user->email,
                ['login_type' => $loginType],
                $ipAddress,
                $userAgent,
                true
            );

            // Determine redirect URL based on role
            $redirectUrl = $this->determineRedirectUrl($user);

            return AuthResponse::success(
                'OAuth login successful',
                $user
            )->withRedirect($redirectUrl)
                ->withData('login_time', Time::now()->toDateTimeString());
        } catch (Exception $e) {
            log_message('error', 'OAuth login error: ' . $e->getMessage());
            return AuthResponse::error('An OAuth login error occurred');
        }
    }
    /**
     * Login user via OAuth provider (Google, Facebook, etc.)
     */
    public function loginWithOAuth(array $oauthData, string $provider = 'google'): AuthResponse
    {
        try {
            // Validate OAuth data
            if (empty($oauthData['email'])) {
                return AuthResponse::error('OAuth data incomplete', ['email' => 'Email is required from OAuth provider']);
            }

            // Find existing user by email
            $user = $this->userModel->where('email', $oauthData['email'])->first();
            if (!$user) {
                // Generate strong password for OAuth user
                $oauthPassword = $this->generateOAuthPassword();

                // Clean username for OAuth (remove special chars, ensure uniqueness)
                $baseUsername = $oauthData['username'] ?? explode('@', $oauthData['email'])[0];
                $cleanUsername = $this->generateUniqueUsername($baseUsername);
                // Auto-register new OAuth user using fromArray method
                $registerData = [
                    'email' => $oauthData['email'],
                    'username' => $cleanUsername,
                    'fullname' => $oauthData['name'] ?? $oauthData['fullname'] ?? $oauthData['email'],
                    'password' => $oauthPassword,
                    'pass_confirm' => $oauthPassword, // Same as password for OAuth
                    'group' => $oauthData['role'] ?? 'asesi',
                    'ip_address' => service('request')->getIPAddress(),
                    'user_agent' => service('request')->getUserAgent(),
                    'google_id' => $oauthData['id'] ?? null // Direct assignment instead of additional_data
                ];

                $registerRequest = RegisterRequest::fromArray($registerData);
                // Set group before save to ensure user masuk ke role yang benar
                $this->userMythModel->withGroup($registerRequest->group ?? 'asesi');
                $registerResponse = $this->register($registerRequest);

                if (!$registerResponse->isSuccess()) {
                    return $registerResponse;
                }
                $user = $registerResponse->user;

                // Pastikan user punya ID setelah registrasi
                if (empty($user->id)) {
                    // Cari user yang baru dibuat berdasarkan email
                    $freshUser = $this->userModel->where('email', $oauthData['email'])->first();
                    if ($freshUser && isset($freshUser->id)) {
                        $user = $freshUser;
                        log_message('debug', '[OAuth] Found newly created user with ID: ' . $user->id);
                    } else {
                        return AuthResponse::error('Failed to get user ID after OAuth registration');
                    }
                }
            }

            // Ensure user is instance of our User entity
            if (!($user instanceof User)) {
                $user = new User((array)$user);
            }

            // Pastikan user ID valid sebelum lanjut
            if (empty($user->id)) {
                // Coba cari user berdasarkan email sebagai fallback
                $fallbackUser = $this->userModel->where('email', $oauthData['email'])->first();
                if ($fallbackUser && isset($fallbackUser->id)) {
                    $user = $fallbackUser;
                    log_message('debug', '[OAuth] Using fallback user with ID: ' . $user->id);
                } else {
                    return AuthResponse::error('Failed to authenticate user: User ID not found');
                }
            }

            // Clear force password reset for OAuth users and ensure google_id is set
            $needsUpdate = false;
            $updateData = [];

            if ($user->force_pass_reset === true) {
                $updateData['force_pass_reset'] = false;
                $needsUpdate = true;
                log_message('debug', '[OAuth] Will clear force_pass_reset for user: ' . $user->id);
            }

            if (empty($user->google_id) && !empty($oauthData['id'])) {
                $updateData['google_id'] = $oauthData['id'];
                $needsUpdate = true;
                log_message('debug', '[OAuth] Will set google_id for user: ' . $user->id);
            }

            if ($needsUpdate) {
                try {
                    $this->userModel->update($user->id, $updateData);
                    log_message('debug', '[OAuth] Updated user ID ' . $user->id . ' with data: ' . json_encode($updateData));

                    // Reload user from database to get updated values
                    $user = $this->userModel->find($user->id);
                    if (!($user instanceof User)) {
                        $user = new User((array)$user);
                    }
                } catch (Exception $e) {
                    log_message('error', '[OAuth] Failed to update user: ' . $e->getMessage());
                }
            } else {
                log_message('debug', '[OAuth] No update needed for user ID: ' . $user->id . ' - force_pass_reset: ' . ($user->force_pass_reset ? 'true' : 'false') . ', google_id: ' . ($user->google_id ?? 'NULL'));
            }

            // Validate user ID before login
            if (empty($user->id)) {
                // Try to find user by email to get the ID
                $freshUser = $this->userModel->where('email', $oauthData['email'])->first();
                if ($freshUser && isset($freshUser->id)) {
                    $user->id = (int)$freshUser->id;
                } else {
                    return AuthResponse::error('Failed to authenticate user: User ID not found');
                }
            }            // Convert to integer to ensure proper type
            $userId = (int)$user->id;
            if ($userId <= 0) {
                return AuthResponse::error('Invalid user ID for authentication');
            }            // Ensure session is properly initialized before OAuth login
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
                log_message('debug', '[OAuth] Native PHP session started');
            }

            // Manual login for OAuth user with comprehensive error handling
            try {
                log_message('debug', '[OAuth] Attempting loginById for user: ' . $userId);
                $this->authenticator->loginById($userId);
                log_message('debug', '[OAuth] loginById successful');
            } catch (Exception $e) {
                log_message('error', 'OAuth loginById failed: ' . $e->getMessage());

                // Try alternative method - set session manually
                log_message('debug', '[OAuth] Attempting manual session setting as fallback');
                session()->set('logged_in', $userId);
                session()->set('user_id', $userId);

                // Also try to set Myth/Auth session
                try {
                    // Force set the user in the authenticator
                    $this->authenticator->login($user, false);
                } catch (Exception $e2) {
                    log_message('warning', '[OAuth] Fallback login also failed: ' . $e2->getMessage());
                }
            }
            // Set session user secara eksplisit setelah login sukses
            session()->set('logged_in', $user->id);
            session()->set('user_email', $user->email);
            session()->set('roles', model('GroupUserModel')->getRolesByUserId($user->id));
            log_message('debug', 'Session after OAuth login: ' . print_r(session()->get(), true));

            // Bersihkan session/token reset password jika ada
            session()->remove('reset_hash');
            session()->remove('reset_token');
            session()->remove('reset_expires');
            log_message('debug', '[OAuth] Session reset password dibersihkan setelah loginById');            // Log OAuth login with CLI-safe request handling
            $request = service('request');
            $ipAddress = 'CLI';
            $userAgent = 'CLI';

            // Only get IP and user agent if not in CLI mode
            if (!is_cli()) {
                try {
                    $ipAddress = $request->getIPAddress();
                    $userAgent = $request->getUserAgent();
                } catch (Exception $e) {
                    log_message('warning', '[OAuth] Could not get request info: ' . $e->getMessage());
                }
            }
            $this->logActivity(
                'oauth_login',
                $user->id,
                $user->email,
                [
                    'provider' => $provider,
                    'oauth_id' => $oauthData['id'] ?? null
                ],
                $ipAddress,
                $userAgent,
                true
            );

            // Determine redirect URL based on role
            // Ensure we have App\Entities\User before calling determineRedirectUrl
            if (!($user instanceof User)) {
                $userData = $this->userModel->find($user->id);
                if ($userData) {
                    $user = $userData instanceof User ? $userData : new User((array)$userData);
                } else {
                    // Fallback - convert current user data to App\Entities\User
                    $user = new User((array)$user);
                }
            }

            $redirectUrl = $this->determineRedirectUrl($user);

            return AuthResponse::success('OAuth login successful', $user)
                ->withRedirect($redirectUrl)
                ->withData('login_time', Time::now()->toDateTimeString())
                ->withData('oauth_provider', $provider);
        } catch (Exception $e) {
            log_message('error', 'OAuth login error: ' . $e->getMessage());
            return AuthResponse::error('OAuth login failed');
        }
    }
    /**
     * Find user by email
     */
    public function findUserByEmail(string $email): ?User
    {
        $userEntity = $this->userMythModel->where('email', $email)->first();
        return $userEntity ? new User($userEntity->toArray()) : null;
    }

    /**
     * Find user by username
     */
    public function findUserByUsername(string $username): ?User
    {
        $userEntity = $this->userMythModel->where('username', $username)->first();
        return $userEntity ? new User($userEntity->toArray()) : null;
    }

    /**
     * Update user data
     */
    public function updateUserData(int $userId, array $data): bool
    {
        try {
            return $this->userMythModel->update($userId, $data);
        } catch (Exception $e) {
            log_message('error', 'Failed to update user data: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Update user activity timestamp
     */
    public function updateUserActivity(int $userId): bool
    {
        try {
            $this->logActivity(
                'user_activity',
                $userId,
                null,
                [
                    'action' => 'activity_update',
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            );
            return true;
        } catch (Exception $e) {
            log_message('error', 'Failed to update user activity: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Refresh user session data
     */
    public function refreshUserSession(): bool
    {
        try {
            if (!$this->isAuthenticated()) {
                log_message('debug', '[RefreshSession] User not authenticated');
                return false;
            }

            $userId = $this->session->get('logged_in');
            if (!$userId) {
                log_message('debug', '[RefreshSession] No user ID in session');
                return false;
            }

            $user = $this->userMythModel->find($userId);

            if (!$user) {
                log_message('error', '[RefreshSession] User not found in database: ' . $userId);
                return false;
            }

            // Refresh session data safely
            $sessionData = [
                'user_email' => $user->email,
                'user_username' => $user->username,
                'last_activity' => time()
            ];

            // Add roles if GroupUserModel exists
            try {
                $roles = model('GroupUserModel')->getRolesByUserId($userId);
                $sessionData['roles'] = $roles;
            } catch (Exception $e) {
                log_message('warning', '[RefreshSession] Could not get user roles: ' . $e->getMessage());
            }

            $this->session->set($sessionData);
            log_message('debug', '[RefreshSession] Session refreshed successfully for user: ' . $userId);

            return true;
        } catch (Exception $e) {
            log_message('error', '[RefreshSession] Failed to refresh user session: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Safely get a configuration property with fallback
     */
    private function getConfigProperty(string $property, $default = null)
    {
        try {
            if (property_exists($this->config, $property)) {
                return $this->config->{$property};
            }
        } catch (\Exception $e) {
            // Property doesn't exist or can't be accessed
        }

        return $default;
    }

    /**
     * Request password reset - alias for forgotPassword for controller compatibility
     */
    public function requestPasswordReset(PasswordResetRequest $request): AuthResponse
    {
        return $this->forgotPassword($request->email);
    }
    /**
     * Find user by Google ID
     */
    public function findUserByGoogleId(string $googleId): ?User
    {
        try {
            $userData = $this->userMythModel->where('google_id', $googleId)->first();
            if ($userData) {
                return new User($userData->toArray());
            }
            return null;
        } catch (Exception $e) {
            log_message('error', 'Failed to find user by Google ID: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Register OAuth user (enhanced registration for OAuth providers)
     */
    public function registerOAuthUser(RegisterRequest $request): AuthResponse
    {
        try {            // For OAuth users, use simplified registration process
            $userData = $request->toUserArray();

            // Ensure OAuth users are active by default and don't require password reset
            $userData['active'] = 1;
            $userData['activate_hash'] = null;
            $userData['force_pass_reset'] = 0;

            // Create user
            $user = new \Myth\Auth\Entities\User($userData);

            if (!$this->userMythModel->save($user)) {
                $errors = $this->userMythModel->errors();
                return AuthResponse::error('Registration failed', $errors);
            }

            $newUser = $this->findUserByEmail($request->email);

            return AuthResponse::success(
                'OAuth registration successful',
                $newUser,
                null,
                site_url('dashboard')
            );
        } catch (Exception $e) {
            log_message('error', 'OAuth user registration failed: ' . $e->getMessage());
            return AuthResponse::error('Registration failed. Please try again.');
        }
    }

    /**
     * Generate a secure password for OAuth users
     * Creates a password that meets all validation requirements
     */
    private function generateOAuthPassword(): string
    {
        // Generate a password with uppercase, lowercase, numbers, and special chars
        // This ensures it passes all validation rules including strong password requirements
        $uppercase = chr(rand(65, 90));    // A-Z
        $lowercase = chr(rand(97, 122));   // a-z
        $number = chr(rand(48, 57));       // 0-9
        $special = ['!', '@', '#', '$', '%', '^', '&', '*'][rand(0, 7)]; // Special chars

        // Add random characters to make it 12 characters total (secure length)
        $randomChars = '';
        for ($i = 0; $i < 8; $i++) {
            $charType = rand(1, 3);
            switch ($charType) {
                case 1:
                    $randomChars .= chr(rand(65, 90));  // Uppercase
                    break;
                case 2:
                    $randomChars .= chr(rand(97, 122)); // Lowercase
                    break;
                case 3:
                    $randomChars .= chr(rand(48, 57));  // Number
                    break;
            }
        }

        // Combine and shuffle
        $password = $uppercase . $lowercase . $number . $special . $randomChars;
        return str_shuffle($password);
    }

    /**
     * Generate a unique username for OAuth users
     * Ensures username meets validation requirements and is unique
     */
    private function generateUniqueUsername(string $baseUsername): string
    {
        // Clean the base username - remove special chars, spaces, etc.
        $cleanBase = preg_replace('/[^a-zA-Z0-9_]/', '', $baseUsername);

        // Ensure minimum length (3 characters)
        if (strlen($cleanBase) < 3) {
            $cleanBase = 'user' . $cleanBase;
        }

        // Ensure maximum length (30 characters - typical username limit)
        if (strlen($cleanBase) > 25) { // Leave room for numeric suffix
            $cleanBase = substr($cleanBase, 0, 25);
        }

        // Check if username exists, if so append numbers
        $username = $cleanBase;
        $counter = 1;

        while ($this->userModel->where('username', $username)->first()) {
            $username = $cleanBase . $counter;
            $counter++;

            // Safety check to prevent infinite loop
            if ($counter > 9999) {
                $username = $cleanBase . time(); // Use timestamp as fallback
                break;
            }
        }

        return $username;
    }
}
