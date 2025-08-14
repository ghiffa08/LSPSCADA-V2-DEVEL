<?php

namespace App\DTOs\Authentication;

/**
 * Password Reset Request DTO
 * 
 * Data Transfer Object for password reset requests
 * Encapsulates password reset data with validation
 */
class PasswordResetRequest
{
    public string $token;
    public string $email;
    public string $password;
    public string $passwordConfirm;
    public string $ipAddress;
    public string $userAgent;

    public function __construct(
        string $token,
        string $email,
        string $password,
        string $passwordConfirm,
        string $ipAddress = '',
        string $userAgent = ''
    ) {
        $this->token = trim($token);
        $this->email = trim(strtolower($email));
        $this->password = $password;
        $this->passwordConfirm = $passwordConfirm;
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
            $data['token'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['pass_confirm'] ?? $data['password_confirm'] ?? '',
            $data['ip_address'] ?? '',
            $data['user_agent'] ?? ''
        );
    }

    /**
     * Validate the request data
     *
     * @return array Array of validation errors
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->token)) {
            $errors['token'] = 'Reset token is required';
        }

        if (empty($this->email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please provide a valid email address';
        }

        if (empty($this->password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (!$this->isStrongPassword($this->password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
        }

        if (empty($this->passwordConfirm)) {
            $errors['pass_confirm'] = 'Password confirmation is required';
        } elseif ($this->password !== $this->passwordConfirm) {
            $errors['pass_confirm'] = 'Password confirmation must match the password';
        }

        return $errors;
    }

    /**
     * Check if password meets strength requirements
     *
     * @param string $password
     * @return bool
     */
    private function isStrongPassword(string $password): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
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
