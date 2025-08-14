<?php

namespace App\DTOs\Authentication;

use App\Entities\User;

/**
 * Authentication Response DTO
 * 
 * Standardized response object for authentication operations
 * Provides consistent structure for all auth-related responses
 */
class AuthResponse
{
    public bool $success;
    public string $message;
    public ?User $user;
    public ?string $token;
    public ?string $redirectUrl;
    public array $errors;
    public int $statusCode;
    public array $data;

    public function __construct(
        bool $success,
        string $message = '',
        ?User $user = null,
        ?string $token = null,
        ?string $redirectUrl = null,
        array $errors = [],
        int $statusCode = 200,
        array $data = []
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->user = $user;
        $this->token = $token;
        $this->redirectUrl = $redirectUrl;
        $this->errors = $errors;
        $this->statusCode = $statusCode;
        $this->data = $data;
    }

    /**
     * Create successful response
     *
     * @param string $message
     * @param User|null $user
     * @param string|null $token
     * @param string|null $redirectUrl
     * @param array $data
     * @return self
     */
    public static function success(
        string $message = 'Operation successful',
        ?User $user = null,
        ?string $token = null,
        ?string $redirectUrl = null,
        array $data = []
    ): self {
        return new self(
            success: true,
            message: $message,
            user: $user,
            token: $token,
            redirectUrl: $redirectUrl,
            errors: [],
            statusCode: 200,
            data: $data
        );
    }

    /**
     * Create error response
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @param array $data
     * @return self
     */
    public static function error(
        string $message,
        array $errors = [],
        int $statusCode = 400,
        array $data = []
    ): self {
        return new self(
            success: false,
            message: $message,
            user: null,
            token: null,
            redirectUrl: null,
            errors: $errors,
            statusCode: $statusCode,
            data: $data
        );
    }

    /**
     * Create validation error response
     *
     * @param array $errors
     * @param string $message
     * @return self
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): self {
        return new self(
            success: false,
            message: $message,
            user: null,
            token: null,
            redirectUrl: null,
            errors: $errors,
            statusCode: 422,
            data: []
        );
    }

    /**
     * Create unauthorized response
     *
     * @param string $message
     * @return self
     */
    public static function unauthorized(string $message = 'Unauthorized access'): self
    {
        return new self(
            success: false,
            message: $message,
            user: null,
            token: null,
            redirectUrl: null,
            errors: [],
            statusCode: 401,
            data: []
        );
    }

    /**
     * Create forbidden response
     *
     * @param string $message
     * @return self
     */
    public static function forbidden(string $message = 'Access forbidden'): self
    {
        return new self(
            success: false,
            message: $message,
            user: null,
            token: null,
            redirectUrl: null,
            errors: [],
            statusCode: 403,
            data: []
        );
    }

    /**
     * Create rate limited response
     *
     * @param string $message
     * @param int $retryAfter
     * @return self
     */
    public static function rateLimited(string $message = 'Too many requests', int $retryAfter = 60): self
    {
        return new self(
            success: false,
            message: $message,
            user: null,
            token: null,
            redirectUrl: null,
            errors: [],
            statusCode: 429,
            data: ['retry_after' => $retryAfter]
        );
    }

    /**
     * Add additional data to response
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function withData(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Set redirect URL
     *
     * @param string $url
     * @return self
     */
    public function withRedirect(string $url): self
    {
        $this->redirectUrl = $url;
        return $this;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'status_code' => $this->statusCode
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        if ($this->user) {
            $response['user'] = [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'nama_lengkap' => $this->user->nama_lengkap ?? '',
                'active' => $this->user->active,
                'roles' => $this->user->getRoles() ?? []
            ];
        }

        if ($this->token) {
            $response['token'] = $this->token;
        }

        if ($this->redirectUrl) {
            $response['redirect_url'] = $this->redirectUrl;
        }

        return $response;
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Check if response has errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Get first error message
     *
     * @return string|null
     */
    public function getFirstError(): ?string
    {
        return !empty($this->errors) ? reset($this->errors) : null;
    }

    /**
     * Check if the response is successful
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get the response message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the redirect URL
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * Check if user requires password reset
     *
     * @return bool
     */
    public function requiresPasswordReset(): bool
    {
        return isset($this->data['requires_password_reset']) && $this->data['requires_password_reset'] === true;
    }

    /**
     * Get password reset token
     *
     * @return string|null
     */
    public function getPasswordResetToken(): ?string
    {
        return $this->data['password_reset_token'] ?? null;
    }

    /**
     * Check if user requires activation
     *
     * @return bool
     */
    public function requiresActivation(): bool
    {
        return isset($this->data['requires_activation']) && $this->data['requires_activation'] === true;
    }

    /**
     * Get session data
     *
     * @return array|null
     */
    public function getSessionData(): ?array
    {
        return $this->data['session_data'] ?? null;
    }

    /**
     * Get validation or other errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set password reset token
     *
     * @param string $token
     * @return self
     */
    public function withPasswordResetToken(string $token): self
    {
        $this->data['password_reset_token'] = $token;
        return $this;
    }

    /**
     * Mark as requiring password reset
     *
     * @param bool $required
     * @return self
     */
    public function withPasswordResetRequired(bool $required = true): self
    {
        $this->data['requires_password_reset'] = $required;
        return $this;
    }

    /**
     * Mark as requiring activation
     *
     * @param bool $required
     * @return self
     */
    public function withActivationRequired(bool $required = true): self
    {
        $this->data['requires_activation'] = $required;
        return $this;
    }

    /**
     * Set session data
     *
     * @param array $sessionData
     * @return self
     */
    public function withSessionData(array $sessionData): self
    {
        $this->data['session_data'] = $sessionData;
        return $this;
    }
}
