<?php

namespace App\Services\Authentication;

use App\Entities\User;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use App\DTOs\Authentication\AuthResponse;
use App\DTOs\Authentication\LoginRequest;
use App\DTOs\Authentication\RegisterRequest;
use App\DTOs\Authentication\PasswordResetRequest;
use Myth\Auth\Models\UserModel;
use CodeIgniter\I18n\Time;
use Exception;

/**
 * Simple Authentication Service
 * 
 * Simplified authentication service focused on stability
 */
class AuthenticationService
{
    protected $userModel;
    protected $userMythModel;
    protected $groupUserModel;
    protected $authenticator;
    protected $config;
    protected $session;
    protected $db;
    
    public function __construct()
    {
        try {
            $this->config = config('Auth');
            $this->session = service('session');
            $this->db = \Config\Database::connect();
            
            $this->userModel = model(UserModel::class);
            $this->userMythModel = new UserMythModel();
            $this->groupUserModel = new GroupUserModel();

            // Initialize authenticator with proper error handling
            $this->authenticator = new \Myth\Auth\Authentication\LocalAuthenticator($this->config);
            $this->authenticator->setUserModel($this->userMythModel);
            $this->authenticator->setLoginModel(model(\Myth\Auth\Models\LoginModel::class));
            $this->emailService = new \App\Services\EmailService();
            $this->config = config('Auth');
            $this->session = service('session');
            $this->db = \Config\Database::connect();
        } catch (\Exception $e) {
            log_message('error', 'AuthenticationService initialization failed: ' . $e->getMessage());
            
            // Only set authenticator to null if initialization completely fails
            $this->authenticator = null;
        }
    }    /**
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

            // Attempt authentication
            $credentials = [
                $request->getLoginType() => $request->login,
                'password' => $request->password
            ];
            
            if (!$this->authenticator->attempt($credentials, $request->remember)) {
                return AuthResponse::error('Invalid credentials', ['login' => 'Invalid email/username or password']);
            }

            // Get authenticated user
            $user = $this->authenticator->user();
            if (!($user instanceof User)) {
                $user = new User((array)$user);
            }

            // Check if user needs password reset
            if ($user->force_pass_reset === true) {
                return AuthResponse::success('Password reset required', $user)
                    ->withRedirect(site_url('reset-password?token=' . $user->reset_hash));
            }

            // Determine redirect URL based on role
            $redirectUrl = $this->determineRedirectUrl($user);

            return AuthResponse::success('Login successful', $user)
                ->withRedirect($redirectUrl);

        } catch (Exception $e) {
            log_message('error', 'Authentication error: ' . $e->getMessage());
            return AuthResponse::error('Authentication error occurred');
        }
    }    /**
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

            // Create user entity
            $userData = $request->toUserArray();
            $user = new User($userData);

            // Set activation status
            $requireActivation = $this->getConfigProperty('requireActivation');
            if ($requireActivation === null) {
                $user->activate();
            } else {
                $user->generateActivateHash();
            }

            // Save user with group
            $users = $this->userMythModel->withGroup($request->getGroup());
            if (!$users->save($user)) {
                return AuthResponse::error('Failed to create user account', $users->errors());
            }

            // Create role-specific data
            $userId = $user->id ?? $users->getInsertID();
            $this->createRoleSpecificData($userId, $request->getGroup(), $request->additionalData);

            // Send activation email if required
            if ($requireActivation !== null) {
                $activator = service('activator');
                $activator->send($user);
                return AuthResponse::success('Registration successful. Please check your email to activate your account.', $user)
                    ->withRedirect(site_url('login'));
            }

            return AuthResponse::success('Registration successful. You may now log in.', $user)
                ->withRedirect(site_url('login'));

        } catch (Exception $e) {
            log_message('error', 'Registration error: ' . $e->getMessage());
            return AuthResponse::error('A registration error occurred');
        }
    }    /**
     * Log out the current user
     */
    public function logout(): AuthResponse
    {
        try {
            if ($this->authenticator && $this->authenticator->check()) {
                $this->authenticator->logout();
            }
            
            // Clear session
            if ($this->session) {
                $this->session->destroy();
            }
            
            return AuthResponse::success('Logout successful')->withRedirect(site_url('/'));
            
        } catch (Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage());
            
            // Fallback: clear session manually
            if ($this->session) {
                $this->session->destroy();
            }
            
            return AuthResponse::success('Logout successful')->withRedirect(site_url('/'));
        }
    }    /**
     * Initiate password reset process
     */
    public function forgotPassword(string $email): AuthResponse
    {
        try {
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return AuthResponse::validationError(['email' => 'Please provide a valid email address']);
            }

            // Find user
            $user = $this->userModel->where('email', $email)->first();
            if (!$user) {
                // Return success even if user not found (security)
                return AuthResponse::success('If an account with that email exists, password reset instructions have been sent.')
                    ->withRedirect(site_url('login'));
            }

            // Generate reset hash
            $user->generateResetHash();
            $this->userModel->save($user);

            // Send reset email
            $resetter = service('resetter');
            $resetter->send($user);

            return AuthResponse::success('Password reset instructions have been sent to your email.')
                ->withRedirect(site_url('login'));
                
        } catch (Exception $e) {
            log_message('error', 'Password reset error: ' . $e->getMessage());
            return AuthResponse::error('A password reset error occurred');
        }
    }    /**
     * Reset password with token
     */
    public function resetPassword(PasswordResetRequest $request): AuthResponse
    {
        try {
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
                return AuthResponse::error('Invalid reset token or email', ['token' => 'The reset token is invalid or has expired']);
            }

            // Check token expiration
            if (!empty($user->reset_expires) && time() > $user->reset_expires->getTimestamp()) {
                return AuthResponse::error('Reset token has expired', ['token' => 'The reset token has expired. Please request a new one.']);
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

            return AuthResponse::success('Password has been successfully reset. You may now log in with your new password.')
                ->withRedirect(site_url('login'));
                
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
    }    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        try {
            // Check if authenticator was properly initialized
            if (!$this->authenticator) {
                return null;
            }
            
            if (!$this->authenticator->check()) {
                return null;
            }

            $user = $this->authenticator->user();

            // Ensure it's our User entity
            if (!($user instanceof User)) {
                $user = new User((array)$user);
            }

            return $user;
        } catch (\Exception $e) {
            // Log the error but don't crash the application
            log_message('error', 'Error getting authenticated user: ' . $e->getMessage());
            return null;
        }
    }    /**
     * Check if user is authenticated
     */    public function isAuthenticated(): bool
    {
        try {
            // Check if authenticator was properly initialized
            if (!$this->authenticator) {
                log_message('debug', 'isAuthenticated: Authenticator not initialized');
                return false;
            }
            
            // Check authenticator
            $isAuthenticatorCheck = $this->authenticator->check();
            
            // Also check session directly as fallback
            $sessionUserId = $this->session->get('logged_in');
            $sessionIsLoggedIn = $this->session->get('isLoggedIn');
            
            log_message('debug', "isAuthenticated: Authenticator check: " . ($isAuthenticatorCheck ? 'true' : 'false') . 
                       ", Session logged_in: " . ($sessionUserId ?: 'null') . 
                       ", Session isLoggedIn: " . ($sessionIsLoggedIn ? 'true' : 'false'));
            
            // Return true if either method confirms authentication
            return $isAuthenticatorCheck || ($sessionUserId && $sessionIsLoggedIn);
            
        } catch (\Exception $e) {
            // Improved error logging - don't log URL strings as errors
            $errorMessage = $e->getMessage();
            
            // Skip logging if error message looks like a URL or token
            if (!preg_match('/^\/|token=|http/', $errorMessage)) {
                log_message('error', 'Authentication check failed: ' . $errorMessage);
            }
            
            return false;
        }
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
    protected function determineRedirectUrl(User $user): string
    {
        // Check for stored redirect URL first
        $redirectUrl = $this->session->get('redirect_url');
        if ($redirectUrl) {
            $this->session->remove('redirect_url');
            return $redirectUrl;
        }

        // Role-based redirection
        if ($user->isAdmin()) {
            return site_url('admin/dashboard');
        } elseif ($user->isAsesor()) {
            return site_url('asesor/dashboard');
        } elseif ($user->isAsesi()) {
            return site_url('asesi/dashboard');
        }

        // Default fallback
        return site_url('dashboard');
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
                    // Create asesi record with default values
                    $asesiData = array_merge([
                        'id_user' => $userId,
                        'nik' => '',
                        'tempat_lahir' => '',
                        'tanggal_lahir' => null,
                        'jenis_kelamin' => null,
                        'pendidikan_terakhir' => null,
                        'no_hp' => null,
                        'created_at' => Time::now(),
                        'updated_at' => Time::now()
                    ], $additionalData);

                    $db->table('asesi')->insert($asesiData);
                    break;

                case 'asesor':
                    // Create asesor record if needed
                    $asesorData = array_merge([
                        'id_user' => $userId,
                        'created_at' => Time::now(),
                        'updated_at' => Time::now()
                    ], $additionalData);

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
            }            // Ensure user is instance of our User entity
            if (!($user instanceof User)) {
                $user = new User((array)$user);
            }            // Check if authenticator is properly initialized
            if (!$this->authenticator) {
                log_message('error', 'OAuth login failed: Authenticator not initialized');
                return AuthResponse::error('Authentication service not available');
            }            // Login the user using Myth/Auth's standard login method
            $loginSuccess = $this->authenticator->login($user, false);
            
            log_message('debug', "OAuth loginById: Authenticator login result: " . ($loginSuccess ? 'true' : 'false'));

            if (!$loginSuccess) {
                $authError = $this->authenticator->error();
                log_message('error', 'OAuth loginById failed: ' . ($authError ?: 'Unknown error'));
                $this->logActivity(
                    'oauth_login',
                    $user->id,
                    $user->email,
                    ['reason' => 'Login failed', 'login_type' => $loginType, 'error' => $authError],
                    $ipAddress,
                    $userAgent,
                    false
                );

                return AuthResponse::error('Login failed: ' . ($authError ?: 'Unknown error'));
            }

            // Force session save
            $this->session->markAsFlashdata('temp');
            $this->session->save();
            
            // Verify login was successful by checking session immediately
            $sessionCheck = $this->authenticator->check();
            $sessionUserId = $this->session->get('logged_in');
            
            log_message('debug', "OAuth loginById: Post-login check - Auth: " . ($sessionCheck ? 'true' : 'false') . 
                       ", Session user ID: " . ($sessionUserId ?: 'null'));

            if (!$sessionCheck) {
                log_message('error', 'OAuth loginById: Login succeeded but authentication check failed');
                
                // Manual session setup as emergency fallback
                $this->session->set([
                    'logged_in' => $user->id,
                    'isLoggedIn' => true,
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                $this->session->markAsFlashdata('temp');
                $this->session->save();
                
                log_message('info', "OAuth loginById: Emergency session setup for user {$user->id}");
            }

            // Additional session verification
            if (!$sessionUserId || $sessionUserId != $user->id) {
                log_message('warning', "OAuth loginById: Session user ID mismatch. Expected: {$user->id}, Got: " . ($sessionUserId ?: 'null'));
                
                // Force correct session
                $this->session->set([
                    'logged_in' => $user->id,
                    'isLoggedIn' => true,
                    'user_id' => $user->id,
                    'user_email' => $user->email
                ]);
                $this->session->save();
                
                log_message('info', "OAuth loginById: Corrected session for user {$user->id}");
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
                ->withData('login_time', Time::now()->toDateTimeString());        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Filter out URL-like error messages
            if (preg_match('/^\/|token=|http|reset-password|password-reset/', $errorMessage)) {
                log_message('info', 'OAuth login: Suppressed URL-like error message for security');
                return AuthResponse::error('OAuth login failed. Please try again.');
            }
            
            // Only log legitimate error messages
            if (!empty($errorMessage) && strlen($errorMessage) > 5) {
                log_message('error', 'OAuth login error: ' . $errorMessage);
            }
            
            return AuthResponse::error('OAuth login failed. Please try again.');
        }
    }    /**
     * Get current authenticated user
     */    public function getCurrentUser(): ?User
    {
        try {
            // Check authentication first
            if (!$this->isAuthenticated()) {
                log_message('debug', 'getCurrentUser: User not authenticated');
                return null;
            }

            // Get user ID from session
            $userId = $this->session->get('logged_in');
            if (!$userId) {
                log_message('debug', 'getCurrentUser: No logged_in session found');
                return null;
            }
            
            log_message('debug', "getCurrentUser: Found user ID in session: {$userId}");
            
            $userEntity = $this->userMythModel->find($userId);
            if (!$userEntity) {
                log_message('warning', "getCurrentUser: User {$userId} not found in database");
                return null;
            }
            
            log_message('debug', "getCurrentUser: Successfully retrieved user: {$userEntity->email}");
            return $userEntity;
        } catch (\Exception $e) {
            // Log the error but don't crash the application
            log_message('error', 'Error getting current user: ' . $e->getMessage());
            return null;
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
                return false;
            }

            $userId = $this->session->get('logged_in');
            $user = $this->userMythModel->find($userId);

            if (!$user) {
                return false;
            }

            // Refresh session data
            $this->session->set([
                'user_email' => $user->email,
                'user_username' => $user->username,
                'last_activity' => time()
            ]);

            return true;
        } catch (Exception $e) {
            log_message('error', 'Failed to refresh user session: ' . $e->getMessage());
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
    }}
