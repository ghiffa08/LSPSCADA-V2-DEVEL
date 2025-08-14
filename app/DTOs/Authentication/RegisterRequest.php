<?php

namespace App\DTOs\Authentication;

/**
 * Register Request DTO
 * 
 * Data Transfer Object for user registration requests
 * Encapsulates registration data with validation and role assignment
 */
class RegisterRequest
{
    public string $username;
    public string $email;
    public string $password;
    public string $passwordConfirm;
    public string $namaLengkap;
    public string $group; // Changed from role to group
    public string $ipAddress;
    public string $userAgent;
    public ?string $googleId; // Add google_id property
    public array $additionalData;
    public function __construct(
        string $username,
        string $email,
        string $password,
        string $passwordConfirm,
        string $namaLengkap,
        string $group = 'asesi', // Default group is asesi
        string $ipAddress = '',
        string $userAgent = '',
        ?string $googleId = null, // Add google_id parameter
        array $additionalData = []
    ) {
        $this->username = trim($username);
        $this->email = trim(strtolower($email));
        $this->password = $password;
        $this->passwordConfirm = $passwordConfirm;
        $this->namaLengkap = trim($namaLengkap);
        $this->group = $group; // Store group name instead of role
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->googleId = $googleId; // Assign google_id
        $this->additionalData = $additionalData;
    }

    /**
     * Create from array data
     *
     * @param array $data
     * @return self
     */    public static function fromArray(array $data): self
    {
        // Convert role to group if role is provided for backward compatibility
        $group = $data['group'] ?? $data['role'] ?? 'asesi';

        return new self(
            $data['username'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['pass_confirm'] ?? $data['password_confirm'] ?? '',
            $data['nama_lengkap'] ?? $data['fullname'] ?? '',
            $group,
            $data['ip_address'] ?? '',
            $data['user_agent'] ?? '',
            $data['google_id'] ?? null, // Add google_id parameter
            $data['additional_data'] ?? []
        );
    }
    /**
     * Validate the request data
     *
     * @return array Array of validation errors
     */    public function validate(): array
    {
        $errors = [];
        $isOAuthUser = !empty($this->googleId); // Check direct googleId property

        // Username validation - relaxed for OAuth users
        if (empty($this->username)) {
            $errors['username'] = 'Username is required';
        } elseif (strlen($this->username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters';
        } elseif (strlen($this->username) > 30) {
            $errors['username'] = 'Username cannot exceed 30 characters';
        } elseif (!$isOAuthUser && !preg_match('/^[a-zA-Z0-9\s]+$/', $this->username)) {
            // More relaxed username validation for OAuth (allow dots, underscores)
            $errors['username'] = 'Username can only contain letters, numbers, and spaces';
        }

        // Email validation
        if (empty($this->email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please provide a valid email address';
        }

        // Name validation
        if (empty($this->namaLengkap)) {
            $errors['nama_lengkap'] = 'Full name is required';
        } elseif (strlen($this->namaLengkap) < 3) {
            $errors['nama_lengkap'] = 'Full name must be at least 3 characters';
        }

        // Password validation - skip for OAuth users
        if (!$isOAuthUser) {
            if (empty($this->password)) {
                $errors['password'] = 'Password is required';
            } elseif (strlen($this->password) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            } elseif (!$this->isStrongPassword($this->password)) {
                $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character';
            }

            // Password confirmation validation
            if (empty($this->passwordConfirm)) {
                $errors['pass_confirm'] = 'Password confirmation is required';
            } elseif ($this->password !== $this->passwordConfirm) {
                $errors['pass_confirm'] = 'Password confirmation must match the password';
            }
        }

        // Group validation - valid groups: admin, asesi, asesor
        $allowedGroups = ['admin', 'asesi', 'asesor'];
        if (!in_array($this->group, $allowedGroups)) {
            $errors['group'] = 'Invalid group specified';
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
     * Convert to array for user creation
     *
     * @return array
     */    public function toUserArray(): array
    {
        // For OAuth users, ensure they're active by default
        $isOAuthUser = !empty($this->googleId); // Check direct googleId property

        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'nama_lengkap' => $this->namaLengkap,
            'active' => $isOAuthUser ? 1 : 0, // OAuth users are active, others might need activation
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'google_id' => $this->googleId, // Use direct property
            // role field is removed as we're using groups
        ];
    }

    /**
     * Convert to array
     *
     * @return array
     */    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'nama_lengkap' => $this->namaLengkap,
            'group' => $this->group, // Return group instead of role
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'google_id' => $this->googleId, // Add google_id
            'additional_data' => $this->additionalData
        ];
    }

    /**
     * Get the group name to assign to the user
     *
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
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
