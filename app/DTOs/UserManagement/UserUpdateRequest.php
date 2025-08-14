<?php

namespace App\DTOs\UserManagement;

use App\DTOs\BaseDTO;

/**
 * User Update Request DTO
 * 
 * Handles user update data with selective field updates
 * and validation for existing user modifications.
 */
class UserUpdateRequest extends BaseDTO
{
    public int $userId;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $passwordConfirm = null;
    public ?string $nama_lengkap = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phoneNumber = null;
    public ?string $group = null;
    public ?string $role = null;
    public ?bool $isActive = null;
    public ?bool $forcePasswordReset = null;
    public ?array $additionalData = null;
    public ?array $permissions = null;
    public ?array $metadata = null;
    public bool $updatePassword = false;
    public array $fieldsToUpdate = [];

    /**
     * Validation rules for user updates
     */
    public function validationRules(): array
    {
        $rules = [
            'userId' => [
                'required',
                'integer',
                'greater_than[0]'
            ]
        ];

        // Add conditional rules only for fields being updated
        if ($this->username !== null) {
            $rules['username'] = [
                'min_length[3]',
                'max_length[50]',
                'alpha_numeric_punct',
                "is_unique[users.username,id,{$this->userId}]"
            ];
        }

        if ($this->email !== null) {
            $rules['email'] = [
                'valid_email',
                'max_length[255]',
                "is_unique[users.email,id,{$this->userId}]"
            ];
        }

        if ($this->password !== null || $this->updatePassword) {
            $rules['password'] = [
                'required',
                'min_length[8]',
                'max_length[255]',
                'strong_password'
            ];
            $rules['passwordConfirm'] = [
                'required',
                'matches[password]'
            ];
        }
        if ($this->nama_lengkap !== null) {
            $rules['nama_lengkap'] = [
                'min_length[2]',
                'max_length[100]'
            ];
        }

        if ($this->phoneNumber !== null) {
            $rules['phoneNumber'] = [
                'permit_empty',
                'min_length[10]',
                'max_length[15]',
                'numeric'
            ];
        }

        if ($this->group !== null) {
            $rules['group'] = [
                'in_list[admin,asesor,asesi]'
            ];
        }

        return $rules;
    }

    /**
     * Custom validation messages
     */
    public function validationMessages(): array
    {
        return [
            'userId' => [
                'required' => 'User ID is required',
                'integer' => 'User ID must be a valid number',
                'greater_than' => 'User ID must be greater than 0'
            ],
            'username' => [
                'min_length' => 'Username must be at least 3 characters',
                'max_length' => 'Username cannot exceed 50 characters',
                'alpha_numeric_punct' => 'Username can only contain letters, numbers, and basic punctuation',
                'is_unique' => 'Username is already taken'
            ],
            'email' => [
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
                'min_length' => 'First name must be at least 2 characters',
                'alpha_space' => 'First name can only contain letters and spaces'
            ],
            'lastName' => [
                'min_length' => 'Last name must be at least 2 characters',
                'alpha_space' => 'Last name can only contain letters and spaces'
            ],
            'phoneNumber' => [
                'min_length' => 'Phone number must be at least 10 digits',
                'numeric' => 'Phone number can only contain numbers'
            ],
            'role' => [
                'in_list' => 'Role must be one of: admin, asesor, asesi'
            ]
        ];
    }

    /**
     * Set which fields should be updated
     */
    public function setFieldsToUpdate(array $fields): void
    {
        $this->fieldsToUpdate = $fields;
    }

    /**
     * Check if a specific field should be updated
     */
    public function shouldUpdateField(string $field): bool
    {
        return in_array($field, $this->fieldsToUpdate) ||
            (property_exists($this, $field) && $this->$field !== null);
    }

    /**
     * Get only the fields that should be updated
     */
    public function getUpdateData(): array
    {
        $updateData = [];

        if ($this->shouldUpdateField('username') && $this->username !== null) {
            $updateData['username'] = $this->username;
        }

        if ($this->shouldUpdateField('email') && $this->email !== null) {
            $updateData['email'] = $this->email;
        }

        if ($this->shouldUpdateField('password') && $this->password !== null) {
            $updateData['password_hash'] = password_hash($this->password, PASSWORD_DEFAULT);
        }

        if ($this->shouldUpdateField('firstName') && $this->firstName !== null) {
            $updateData['first_name'] = $this->firstName;
        }

        if ($this->shouldUpdateField('lastName') && $this->lastName !== null) {
            $updateData['last_name'] = $this->lastName;
        }

        if ($this->shouldUpdateField('phoneNumber')) {
            $updateData['phone_number'] = $this->phoneNumber;
        }

        if ($this->shouldUpdateField('role') && $this->role !== null) {
            $updateData['role'] = $this->role;
        }

        if ($this->shouldUpdateField('isActive') && $this->isActive !== null) {
            $updateData['active'] = $this->isActive ? 1 : 0;
        }

        if ($this->shouldUpdateField('forcePasswordReset') && $this->forcePasswordReset !== null) {
            $updateData['force_pass_reset'] = $this->forcePasswordReset ? 1 : 0;
        }

        if ($this->shouldUpdateField('permissions') && $this->permissions !== null) {
            $updateData['permissions'] = json_encode($this->permissions);
        }

        if ($this->shouldUpdateField('metadata') && $this->metadata !== null) {
            $updateData['metadata'] = json_encode($this->metadata);
        }

        // Always update the updated_at timestamp
        $updateData['updated_at'] = date('Y-m-d H:i:s');

        return $updateData;
    }

    /**
     * Get full name if both first and last names are provided
     */
    public function getFullName(): ?string
    {
        if ($this->firstName !== null && $this->lastName !== null) {
            return trim($this->firstName . ' ' . $this->lastName);
        }
        return null;
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
     * Check if role change requires elevated permissions
     */
    public function requiresElevatedPermissions(): bool
    {
        return $this->group !== null && in_array($this->group, ['admin', 'asesor']);
    }

    /**
     * Check if this is a sensitive update
     */
    public function isSensitiveUpdate(): bool
    {
        return $this->shouldUpdateField('group') ||
            $this->shouldUpdateField('isActive') ||
            $this->shouldUpdateField('additionalData') ||
            $this->shouldUpdateField('password');
    }

    /**
     * Get audit log message for this update
     */
    public function getAuditMessage(): string
    {
        $updatedFields = [];

        if ($this->shouldUpdateField('username')) $updatedFields[] = 'username';
        if ($this->shouldUpdateField('email')) $updatedFields[] = 'email';
        if ($this->shouldUpdateField('password')) $updatedFields[] = 'password';
        if ($this->shouldUpdateField('nama_lengkap')) $updatedFields[] = 'nama lengkap';
        if ($this->shouldUpdateField('phoneNumber')) $updatedFields[] = 'phone number';
        if ($this->shouldUpdateField('group')) $updatedFields[] = 'group';
        if ($this->shouldUpdateField('isActive')) $updatedFields[] = 'account status';
        if ($this->shouldUpdateField('forcePasswordReset')) $updatedFields[] = 'password reset flag';
        if ($this->shouldUpdateField('additionalData')) $updatedFields[] = 'additional data';

        return 'Updated user fields: ' . implode(', ', $updatedFields);
    }

    /**
     * Convert to update array
     */
    public function toUpdateArray(): array
    {
        $data = [];

        if ($this->username !== null) $data['username'] = $this->username;
        if ($this->email !== null) $data['email'] = $this->email;
        if ($this->password !== null) $data['password'] = $this->password;
        if ($this->nama_lengkap !== null) $data['nama_lengkap'] = $this->nama_lengkap;
        if ($this->isActive !== null) $data['active'] = $this->isActive ? 1 : 0;
        if ($this->forcePasswordReset !== null) $data['force_pass_reset'] = $this->forcePasswordReset ? 1 : 0;

        return $data;
    }

    /**
     * Validate with user ID context
     */
    public function validate(int $userId = null): array
    {
        if ($userId !== null) {
            $this->userId = $userId;
        }

        return parent::validate();
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data, int $userId = null): self
    {
        $instance = new self();

        if ($userId !== null) {
            $instance->userId = $userId;
        }

        $instance->username = $data['username'] ?? null;
        $instance->email = $data['email'] ?? null;
        $instance->password = $data['password'] ?? null;
        $instance->passwordConfirm = $data['password_confirm'] ?? $data['passwordConfirm'] ?? null;
        $instance->nama_lengkap = $data['nama_lengkap'] ?? null;
        $instance->phoneNumber = $data['phoneNumber'] ?? $data['phone_number'] ?? null;
        $instance->group = $data['group'] ?? $data['role'] ?? null;
        $instance->isActive = isset($data['isActive']) ? (bool)$data['isActive'] : (isset($data['active']) ? (bool)$data['active'] : null);
        $instance->forcePasswordReset = isset($data['forcePasswordReset']) ? (bool)$data['forcePasswordReset'] : (isset($data['force_pass_reset']) ? (bool)$data['force_pass_reset'] : null);
        $instance->additionalData = $data['additionalData'] ?? $data['additional_data'] ?? null;
        $instance->metadata = $data['metadata'] ?? null;
        $instance->updatePassword = (bool)($data['updatePassword'] ?? $data['update_password'] ?? false);

        return $instance;
    }
}
