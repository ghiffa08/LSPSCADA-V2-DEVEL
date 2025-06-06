<?php
// Test the dashboard routing logic without full framework bootstrap
echo "=== Dashboard Routing Test ===\n\n";

// Simulate user data from database
$testUsers = [
    ['id' => 4, 'username' => 'asesi1', 'expected_role' => 'Asesi', 'expected_redirect' => 'asesi/dashboard'],
    ['id' => 5, 'username' => 'asesi2', 'expected_role' => 'Asesi', 'expected_redirect' => 'asesi/dashboard'],
    ['id' => 1, 'username' => 'admin1', 'expected_role' => 'Admin', 'expected_redirect' => 'admin/dashboard'],
    ['id' => 7, 'username' => 'asesor1', 'expected_role' => 'Asesor', 'expected_redirect' => 'asesor/dashboard'],
];

// Simulate role assignments based on our database state
$roleAssignments = [
    1 => ['Admin'],
    2 => ['Admin'],
    3 => ['Admin'],
    4 => ['Asesi'],
    5 => ['Asesi'],
    6 => ['Asesi'],
    7 => ['Asesor'],
    8 => ['Asesor'],
    9 => ['Asesor'],
    16 => ['Asesi'],
];

// Simple User Entity simulation
class TestUser
{
    private $id;
    private $roles;

    public function __construct($id, $roles)
    {
        $this->id = $id;
        $this->roles = $roles;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtolower($role), array_map('strtolower', $this->roles ?? []));
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isAsesor(): bool
    {
        return $this->hasRole('asesor');
    }

    public function isAsesi(): bool
    {
        return $this->hasRole('asesi');
    }
}

// Dashboard Router simulation
function routeUser($user)
{
    if ($user->isAdmin()) {
        return 'admin/dashboard';
    }
    if ($user->isAsesor()) {
        return 'asesor/dashboard';
    }
    if ($user->isAsesi()) {
        return 'asesi/dashboard';
    }
    return 'default/dashboard';
}

// Test each user
foreach ($testUsers as $testUser) {
    $userId = $testUser['id'];
    $userRoles = $roleAssignments[$userId] ?? [];

    echo "Testing User ID: {$userId} ({$testUser['username']})\n";
    echo "Expected Role: {$testUser['expected_role']}\n";
    echo "Expected Redirect: {$testUser['expected_redirect']}\n";

    // Create test user entity
    $user = new TestUser($userId, $userRoles);

    // Test role detection
    echo "Roles: " . implode(', ', $userRoles) . "\n";
    echo "isAdmin(): " . ($user->isAdmin() ? 'TRUE' : 'FALSE') . "\n";
    echo "isAsesor(): " . ($user->isAsesor() ? 'TRUE' : 'FALSE') . "\n";
    echo "isAsesi(): " . ($user->isAsesi() ? 'TRUE' : 'FALSE') . "\n";

    // Test routing
    $actualRedirect = routeUser($user);
    echo "Actual Redirect: {$actualRedirect}\n";
    echo "Test Result: " . ($actualRedirect === $testUser['expected_redirect'] ? 'PASS' : 'FAIL') . "\n";
    echo "---\n";
}

echo "\n=== Test Complete ===\n";
