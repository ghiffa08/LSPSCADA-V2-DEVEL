<?php

namespace App\DTOs\Authentication;

/**
 * Login Request DTO
 * 
 * Data Transfer Object for login requests
 * Encapsulates login credential data with validation
 */
class LoginRequest
{
    public string $login;
    public string $password;
    public bool $remember;
    public string $ipAddress;
    public string $userAgent;

    public function __construct(
        string $login,
        string $password,
        bool $remember = false,
        string $ipAddress = '',
        string $userAgent = ''
    ) {
        $this->login = trim($login);
        $this->password = $password;
        $this->remember = $remember;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    /**
     * Create from array data
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['login'] ?? '',
            $data['password'] ?? '',
            (bool)($data['remember'] ?? false),
            $data['ip_address'] ?? '',
            $data['user_agent'] ?? ''
        );
    }

    /**
     * Get login type (email or username)
     *
     * @return string
     */
    public function getLoginType(): string
    {
        return filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    /**
     * Validate the request data
     *
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->login)) {
            $errors['login'] = 'Login field is required';
        }

        if (empty($this->password)) {
            $errors['password'] = 'Password field is required';
        }

        if ($this->getLoginType() === 'email' && !filter_var($this->login, FILTER_VALIDATE_EMAIL)) {
            $errors['login'] = 'Please provide a valid email address';
        }

        return $errors;
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'login' => $this->login,
            'password' => $this->password,
            'remember' => $this->remember,
            'login_type' => $this->getLoginType(),
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent
        ];
    }

    /**
     * Get validation errors (for controller compatibility)
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validate();
    }
}
