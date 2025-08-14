<?php

namespace App\DTOs\UserManagement;

use App\DTOs\BaseDTO;
use App\Entities\User;

/**
 * User Response DTO
 * 
 * Standardized response object for user data with security-aware
 * field filtering and comprehensive user information presentation.
 */
class UserResponse extends BaseDTO
{
    public int $id;
    public string $username;
    public string $email;
    public string $firstName;
    public string $lastName;
    public string $fullName;
    public ?string $phoneNumber;
    public string $role;
    public string $roleDisplay;
    public bool $isActive;
    public bool $isVerified;
    public bool $forcePasswordReset;
    public ?string $lastLogin;
    public string $createdAt;
    public string $updatedAt;
    public array $permissions;
    public array $metadata;
    public array $statistics;
    public bool $canEdit;
    public bool $canDelete;
    public bool $canChangeRole;

    /**
     * Create UserResponse from User entity
     */
    public static function fromEntity(User $user, ?User $currentUser = null): self
    {
        $instance = new self();

        $instance->id = $user->id;
        $instance->username = $user->username;
        $instance->email = $user->email;
        $instance->firstName = $user->first_name ?? '';
        $instance->lastName = $user->last_name ?? '';
        $instance->fullName = trim($instance->firstName . ' ' . $instance->lastName);
        $instance->phoneNumber = $user->phone_number;
        $instance->role = $user->role ?? 'asesi';
        $instance->roleDisplay = $instance->getRoleDisplayName($instance->role);
        $instance->isActive = (bool) $user->active;
        $instance->isVerified = !empty($user->activate_hash) ? false : true;
        $instance->forcePasswordReset = (bool) $user->force_pass_reset;
        $instance->lastLogin = $user->last_login ?
            date('Y-m-d H:i:s', strtotime($user->last_login)) : null;
        $instance->createdAt = date('Y-m-d H:i:s', strtotime($user->created_at));
        $instance->updatedAt = date('Y-m-d H:i:s', strtotime($user->updated_at));

        // Parse JSON fields safely
        $instance->permissions = $instance->parseJsonField($user->permissions ?? '[]');
        $instance->metadata = $instance->parseJsonField($user->metadata ?? '{}');

        // Set statistics
        $instance->statistics = $instance->calculateStatistics($user);

        // Set permissions based on current user
        $instance->setUserPermissions($currentUser);

        return $instance;
    }

    /**
     * Create UserResponse from array data
     */
    public static function fromArray(array $data, ?User $currentUser = null): self
    {
        $instance = new self();

        $instance->id = (int) ($data['id'] ?? 0);
        $instance->username = $data['username'] ?? '';
        $instance->email = $data['email'] ?? '';
        $instance->firstName = $data['first_name'] ?? '';
        $instance->lastName = $data['last_name'] ?? '';
        $instance->fullName = trim($instance->firstName . ' ' . $instance->lastName);
        $instance->phoneNumber = $data['phone_number'] ?? null;
        $instance->role = $data['role'] ?? 'asesi';
        $instance->roleDisplay = $instance->getRoleDisplayName($instance->role);
        $instance->isActive = (bool) ($data['active'] ?? false);
        $instance->isVerified = empty($data['activate_hash']);
        $instance->forcePasswordReset = (bool) ($data['force_pass_reset'] ?? false);
        $instance->lastLogin = $data['last_login'] ?? null;
        $instance->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $instance->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');

        // Parse JSON fields safely
        $instance->permissions = $instance->parseJsonField($data['permissions'] ?? '[]');
        $instance->metadata = $instance->parseJsonField($data['metadata'] ?? '{}');

        // Set default statistics
        $instance->statistics = [];

        // Set permissions based on current user
        $instance->setUserPermissions($currentUser);

        return $instance;
    }

    /**
     * Get public safe data (excludes sensitive information)
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->maskEmail($this->email),
            'fullName' => $this->fullName,
            'role' => $this->roleDisplay,
            'isActive' => $this->isActive,
            'isVerified' => $this->isVerified,
            'createdAt' => $this->createdAt
        ];
    }

    /**
     * Get complete user data for authorized users
     */
    public function toCompleteArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => $this->fullName,
            'phoneNumber' => $this->phoneNumber,
            'role' => $this->role,
            'roleDisplay' => $this->roleDisplay,
            'isActive' => $this->isActive,
            'isVerified' => $this->isVerified,
            'forcePasswordReset' => $this->forcePasswordReset,
            'lastLogin' => $this->lastLogin,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'permissions' => $this->permissions,
            'metadata' => $this->metadata,
            'statistics' => $this->statistics,
            'canEdit' => $this->canEdit,
            'canDelete' => $this->canDelete,
            'canChangeRole' => $this->canChangeRole
        ];
    }

    /**
     * Get user data for admin view
     */
    public function toAdminArray(): array
    {
        return array_merge($this->toCompleteArray(), [
            'sensitiveActions' => [
                'canResetPassword' => $this->canEdit,
                'canActivateDeactivate' => $this->canEdit,
                'canViewAuditLog' => true,
                'canImpersonate' => $this->canEdit && $this->role !== 'admin'
            ]
        ]);
    }

    /**
     * Get role display name
     */
    private function getRoleDisplayName(string $role): string
    {
        $roleNames = [
            'admin' => 'Administrator',
            'asesor' => 'Asesor',
            'asesi' => 'Asesi'
        ];

        return $roleNames[$role] ?? ucfirst($role);
    }
    /**
     * Parse JSON field safely
     */
    protected function parseJsonField(string $jsonData): array
    {
        $decoded = json_decode($jsonData, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Calculate user statistics
     */
    private function calculateStatistics(User $user): array
    {
        $stats = [
            'accountAge' => 0,
            'lastLoginDays' => null,
            'loginCount' => 0,
            'assessmentCount' => 0,
            'portfolioCount' => 0
        ];

        // Calculate account age in days
        if ($user->created_at) {
            $createdDate = strtotime($user->created_at);
            $stats['accountAge'] = floor((time() - $createdDate) / (24 * 3600));
        }

        // Calculate days since last login
        if ($user->last_login) {
            $lastLoginDate = strtotime($user->last_login);
            $stats['lastLoginDays'] = floor((time() - $lastLoginDate) / (24 * 3600));
        }

        // Additional statistics would be calculated here
        // This would typically involve querying related tables
        // For now, we'll set defaults

        return $stats;
    }

    /**
     * Set user permissions based on current user context
     */
    private function setUserPermissions(?User $currentUser): void
    {
        if (!$currentUser) {
            $this->canEdit = false;
            $this->canDelete = false;
            $this->canChangeRole = false;
            return;
        }

        $currentUserRole = $currentUser->role ?? 'asesi';

        // Role hierarchy: admin > asesor > asesi
        $roleHierarchy = [
            'admin' => 3,
            'asesor' => 2,
            'asesi' => 1
        ];

        $currentUserLevel = $roleHierarchy[$currentUserRole] ?? 0;
        $targetUserLevel = $roleHierarchy[$this->role] ?? 0;

        // Users can only edit users with lower or equal role level
        // But admins can edit everyone except other admins (unless it's themselves)
        if ($currentUserRole === 'admin') {
            $this->canEdit = true;
            $this->canDelete = $this->id !== $currentUser->id; // Can't delete themselves
            $this->canChangeRole = $this->id !== $currentUser->id; // Can't change their own role
        } else {
            $this->canEdit = $currentUserLevel > $targetUserLevel;
            $this->canDelete = $currentUserLevel > $targetUserLevel && $this->id !== $currentUser->id;
            $this->canChangeRole = false; // Only admins can change roles
        }

        // Users can always edit themselves (limited)
        if ($this->id === $currentUser->id) {
            $this->canEdit = true;
            $this->canDelete = false; // Can't delete themselves
            $this->canChangeRole = false; // Can't change their own role
        }
    }

    /**
     * Mask email address for privacy
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        if (strlen($username) <= 2) {
            return $email;
        }

        $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
        return $maskedUsername . '@' . $domain;
    }

    /**
     * Get status badge information
     */
    public function getStatusBadge(): array
    {
        if (!$this->isActive) {
            return [
                'text' => 'Inactive',
                'class' => 'badge-danger',
                'icon' => 'fa-ban'
            ];
        }

        if (!$this->isVerified) {
            return [
                'text' => 'Unverified',
                'class' => 'badge-warning',
                'icon' => 'fa-exclamation-triangle'
            ];
        }

        if ($this->forcePasswordReset) {
            return [
                'text' => 'Password Reset Required',
                'class' => 'badge-info',
                'icon' => 'fa-key'
            ];
        }

        return [
            'text' => 'Active',
            'class' => 'badge-success',
            'icon' => 'fa-check'
        ];
    }
    /**
     * Get role badge information
     */
    public function getRoleBadge(): array
    {
        $roleBadges = [
            'admin' => [
                'text' => 'Administrator',
                'class' => 'badge-primary',
                'icon' => 'fa-crown'
            ],
            'asesor' => [
                'text' => 'Asesor',
                'class' => 'badge-success',
                'icon' => 'fa-clipboard-check'
            ],
            'asesi' => [
                'text' => 'Asesi',
                'class' => 'badge-info',
                'icon' => 'fa-user'
            ]
        ];

        return $roleBadges[$this->role] ?? [
            'text' => ucfirst($this->role),
            'class' => 'badge-secondary',
            'icon' => 'fa-user'
        ];
    }

    /**
     * Static factory methods for response creation
     */
    public static function success(string $message = 'Success', $data = null): self
    {
        $instance = new self();
        $instance->success = true;
        $instance->message = $message;
        $instance->data = $data;
        $instance->errors = [];
        $instance->code = 200;
        return $instance;
    }

    public static function error(string $message = 'Error', array $errors = [], int $code = 400): self
    {
        $instance = new self();
        $instance->success = false;
        $instance->message = $message;
        $instance->data = null;
        $instance->errors = $errors;
        $instance->code = $code;
        return $instance;
    }

    public static function validationError(array $errors, string $message = 'Validation failed'): self
    {
        $instance = new self();
        $instance->success = false;
        $instance->message = $message;
        $instance->data = null;
        $instance->errors = $errors;
        $instance->code = 422;
        return $instance;
    }

    /**
     * Add additional data to response
     */
    public function withData(string $key, $value): self
    {
        if (!is_array($this->data)) {
            $this->data = [];
        }
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Response properties
     */
    public bool $success = true;
    public string $message = '';
    public $data = null;
    public array $errors = [];
    public int $code = 200;
}
