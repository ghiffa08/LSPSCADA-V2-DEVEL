<?php

namespace App\DTOs\UserManagement;

use App\DTOs\BaseDTO;

/**
 * User Creation Request DTO
 * 
 * Handles user creation data with comprehensive validation
 * and business rule enforcement for new user registration.
 */
class UserCreateRequest extends BaseDTO
{
    public string $username;
    public string $email;
    public string $password;
    public string $passwordConfirm;
    public string $nama_lengkap;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phoneNumber = null;
    public string $group = 'asesi'; // Default group (not role)
    public ?string $role = null; // For compatibility
    public bool $isActive = true;
    public bool $forcePasswordReset = false;
    public array $additionalData = [];
    public array $permissions = [];
    public array $metadata = [];

    /**
     * Validation rules for user creation
     */
    public function validationRules(): array
    {
        return [
            'username' => [
                'required',
                'min_length[3]',
                'max_length[50]',
                'alpha_numeric_punct',
                'is_unique[users.username]'
            ],
            'email' => [
                'required',
                'valid_email',
                'max_length[255]',
                'is_unique[users.email]'
            ],
            'password' => [
                'required',
                'min_length[8]',
                'max_length[255]',
                'strong_password'
            ],
            'passwordConfirm' => [
                'required',
                'matches[password]'
            ],
            'nama_lengkap' => [
                'required',
                'min_length[2]',
                'max_length[100]'
            ],
            'phoneNumber' => [
                'permit_empty',
                'min_length[10]',
                'max_length[15]',
                'numeric'
            ],
            'group' => [
                'required',
                'in_list[admin,asesor,asesi]'
            ],
            'isActive' => [
                'permit_empty',
                'in_list[0,1,true,false]'
            ],
            'forcePasswordReset' => [
                'permit_empty',
                'in_list[0,1,true,false]'
            ]
        ];
    }

    /**
     * Custom validation messages
     */
    public function validationMessages(): array
    {
        return [
            'username' => [
                'required' => 'Username is required',
                'min_length' => 'Username must be at least 3 characters',
                'max_length' => 'Username cannot exceed 50 characters',
                'alpha_numeric_punct' => 'Username can only contain letters, numbers, and basic punctuation',
                'is_unique' => 'Username is already taken'
            ],
            'email' => [
                'required' => 'Email address is required',
                'valid_email' => 'Please provide a valid email address',
                'is_unique' => 'Email address is already registered'
            ],
            'password' => [
                'required' => 'Password is required',
                'min_length' => 'Password must be at least 8 characters',
                'strong_password' => 'Password must contain uppercase, lowercase, number, and special character'
            ],
            'passwordConfirm' => [
                'required' => 'Password confirmation is required',
                'matches' => 'Password confirmation does not match'
            ],
            'firstName' => [
                'required' => 'First name is required',
                'min_length' => 'First name must be at least 2 characters',
                'alpha_space' => 'First name can only contain letters and spaces'
            ],
            'lastName' => [
                'required' => 'Last name is required',
                'min_length' => 'Last name must be at least 2 characters',
                'alpha_space' => 'Last name can only contain letters and spaces'
            ],
            'phoneNumber' => [
                'min_length' => 'Phone number must be at least 10 digits',
                'numeric' => 'Phone number can only contain numbers'
            ],
            'role' => [
                'required' => 'User role is required',
                'in_list' => 'Role must be one of: admin, asesor, asesi'
            ]
        ];
    }
    /**
     * Get full name
     */
    public function getFullName(): string
    {
        if ($this->firstName && $this->lastName) {
            return trim($this->firstName . ' ' . $this->lastName);
        }
        return $this->nama_lengkap;
    }

    /**
     * Check if user should be activated immediately
     */
    public function shouldActivateImmediately(): bool
    {
        return $this->isActive && !$this->forcePasswordReset;
    }
    /**
     * Get user data for entity creation
     */
    public function toUserEntityData(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password_hash' => password_hash($this->password, PASSWORD_DEFAULT),
            'first_name' => $this->firstName ?? '',
            'last_name' => $this->lastName ?? '',
            'phone_number' => $this->phoneNumber,
            'role' => $this->role ?? $this->group,
            'active' => $this->isActive ? 1 : 0,
            'force_pass_reset' => $this->forcePasswordReset ? 1 : 0,
            'permissions' => json_encode($this->permissions ?: []),
            'metadata' => json_encode($this->metadata ?: []),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Sanitize phone number
     */
    public function sanitizePhoneNumber(): void
    {
        if ($this->phoneNumber) {
            // Remove all non-numeric characters
            $this->phoneNumber = preg_replace('/[^0-9]/', '', $this->phoneNumber);

            // Add country code if missing (assuming Indonesia +62)
            if (strlen($this->phoneNumber) >= 10 && !str_starts_with($this->phoneNumber, '62')) {
                if (str_starts_with($this->phoneNumber, '0')) {
                    $this->phoneNumber = '62' . substr($this->phoneNumber, 1);
                } else {
                    $this->phoneNumber = '62' . $this->phoneNumber;
                }
            }
        }
    }
    /**
     * Check if role requires special permissions
     */
    public function requiresElevatedPermissions(): bool
    {
        return in_array($this->group, ['admin', 'asesor']) || in_array($this->role, ['admin', 'asesor']);
    }

    /**
     * Get default permissions based on role
     */
    public function getDefaultPermissions(): array
    {
        $rolePermissions = [
            'admin' => [
                'user.create',
                'user.read',
                'user.update',
                'user.delete',
                'role.manage',
                'system.configure',
                'reports.all',
                'assessment.manage',
                'competency.manage'
            ],
            'asesor' => [
                'user.read',
                'user.update_limited',
                'assessment.create',
                'assessment.evaluate',
                'reports.assessment',
                'competency.view'
            ],
            'asesi' => [
                'profile.update',
                'assessment.take',
                'reports.own',
                'portfolio.manage'
            ]
        ];

        return array_merge(
            $rolePermissions[$this->group] ?? [],
            $this->additionalData
        );
    }

    /**
     * Convert to user array for entity creation
     */
    public function toUserArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'nama_lengkap' => $this->nama_lengkap,
            'active' => $this->isActive ? 1 : 0,
            'force_pass_reset' => $this->forcePasswordReset ? 1 : 0
        ];
    }

    /**
     * Create from array (static factory method)
     */    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->username = $data['username'] ?? '';
        $instance->email = $data['email'] ?? '';
        $instance->password = $data['password'] ?? '';
        $instance->passwordConfirm = $data['password_confirm'] ?? $data['passwordConfirm'] ?? '';
        $instance->nama_lengkap = $data['nama_lengkap'] ?? '';
        $instance->phoneNumber = $data['phoneNumber'] ?? $data['phone_number'] ?? null;
        $instance->group = $data['group'] ?? $data['role'] ?? 'asesi';
        $instance->isActive = (bool)($data['isActive'] ?? $data['active'] ?? true);
        $instance->forcePasswordReset = (bool)($data['forcePasswordReset'] ?? $data['force_pass_reset'] ?? false);
        $instance->additionalData = $data['additionalData'] ?? $data['additional_data'] ?? [];
        $instance->metadata = $data['metadata'] ?? [];

        return $instance;
    }
}
