<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use Myth\Auth\Models\GroupModel;

class ManageUsers extends BaseCommand
{
    protected $group = 'auth';
    protected $name = 'auth:manage-users';
    protected $description = 'Manage users - list, assign roles, activate/deactivate';

    protected $usage = 'auth:manage-users [action] [options]';
    protected $arguments = [
        'action' => 'Action to perform: list, assign-role, activate, deactivate, reset-password'
    ];
    protected $options = [
        '--user-id' => 'User ID for operations',
        '--email' => 'User email for operations',
        '--role' => 'Role to assign (Admin, Asesor, Asesi)',
        '--password' => 'New password for reset-password action',
    ];

    public function run(array $params)
    {
        $action = $params[0] ?? CLI::prompt('Action', ['list', 'assign-role', 'activate', 'deactivate', 'reset-password']);

        switch ($action) {
            case 'list':
                $this->listUsers();
                break;
            case 'assign-role':
                $this->assignRole();
                break;
            case 'activate':
                $this->toggleUserStatus(true);
                break;
            case 'deactivate':
                $this->toggleUserStatus(false);
                break;
            case 'reset-password':
                $this->resetPassword();
                break;
            default:
                CLI::error('Invalid action. Available actions: list, assign-role, activate, deactivate, reset-password');
        }
    }

    private function listUsers()
    {
        $userModel = new UserMythModel();

        $users = $userModel->select('users.*, auth_groups.name as role')
            ->join('auth_groups_users', 'users.id = auth_groups_users.user_id', 'left')
            ->join('auth_groups', 'auth_groups_users.group_id = auth_groups.id', 'left')
            ->orderBy('users.created_at', 'DESC')
            ->findAll();

        if (empty($users)) {
            CLI::write('No users found.', 'yellow');
            return;
        }

        CLI::write('Users List:', 'green');
        CLI::write(str_repeat('=', 80));

        $table = [];
        $table[] = ['ID', 'Username', 'Email', 'Name', 'Role', 'Status', 'Created'];

        foreach ($users as $user) {
            $table[] = [
                $user->id,
                $user->username ?: '-',
                $user->email,
                $user->nama_lengkap ?: '-',
                $user->role ?: 'No Role',
                $user->active ? 'Active' : 'Inactive',
                $user->created_at ? date('Y-m-d', strtotime($user->created_at)) : '-'
            ];
        }

        CLI::table($table);
        CLI::write("Total users: " . count($users));
    }

    private function assignRole()
    {
        $userModel = new UserMythModel();
        $groupModel = new GroupModel();
        $groupUserModel = new GroupUserModel();

        // Get user
        $userId = CLI::getOption('user-id');
        $email = CLI::getOption('email');

        if (!$userId && !$email) {
            $this->listUsers();
            $userId = CLI::prompt('Enter User ID');
        }

        if ($email && !$userId) {
            $user = $userModel->where('email', $email)->first();
            if (!$user) {
                CLI::error('User not found with email: ' . $email);
                return;
            }
            $userId = $user->id;
        } else {
            $user = $userModel->find($userId);
            if (!$user) {
                CLI::error('User not found with ID: ' . $userId);
                return;
            }
        }

        // Get role
        $role = CLI::getOption('role') ?? CLI::prompt('Role to assign', ['Admin', 'Asesor', 'Asesi']);

        $group = $groupModel->where('name', $role)->first();
        if (!$group) {
            CLI::error("Role '{$role}' not found.");
            return;
        }

        // Remove existing role assignments
        $groupUserModel->where('user_id', $userId)->delete();

        // Assign new role
        $assigned = $groupUserModel->insert([
            'user_id' => $userId,
            'group_id' => $group->id
        ]);

        if ($assigned) {
            CLI::write("Successfully assigned role '{$role}' to user '{$user->email}'", 'green');
        } else {
            CLI::error('Failed to assign role.');
        }
    }

    private function toggleUserStatus($activate = true)
    {
        $userModel = new UserMythModel();

        // Get user
        $userId = CLI::getOption('user-id');
        $email = CLI::getOption('email');

        if (!$userId && !$email) {
            $this->listUsers();
            $userId = CLI::prompt('Enter User ID');
        }

        if ($email && !$userId) {
            $user = $userModel->where('email', $email)->first();
            if (!$user) {
                CLI::error('User not found with email: ' . $email);
                return;
            }
            $userId = $user->id;
        } else {
            $user = $userModel->find($userId);
            if (!$user) {
                CLI::error('User not found with ID: ' . $userId);
                return;
            }
        }

        $status = $activate ? 1 : 0;
        $statusText = $activate ? 'activated' : 'deactivated';

        if ($userModel->update($userId, ['active' => $status])) {
            CLI::write("User '{$user->email}' has been {$statusText}.", 'green');
        } else {
            CLI::error("Failed to {$statusText} user.");
        }
    }

    private function resetPassword()
    {
        $userModel = new UserMythModel();

        // Get user
        $userId = CLI::getOption('user-id');
        $email = CLI::getOption('email');

        if (!$userId && !$email) {
            $this->listUsers();
            $userId = CLI::prompt('Enter User ID');
        }

        if ($email && !$userId) {
            $user = $userModel->where('email', $email)->first();
            if (!$user) {
                CLI::error('User not found with email: ' . $email);
                return;
            }
            $userId = $user->id;
        } else {
            $user = $userModel->find($userId);
            if (!$user) {
                CLI::error('User not found with ID: ' . $userId);
                return;
            }
        }

        // Get new password
        $password = CLI::getOption('password') ?? CLI::promptByKey('New Password', ['required' => true], 'password');

        if (strlen($password) < 8) {
            CLI::error('Password must be at least 8 characters long.');
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($userModel->update($userId, ['password_hash' => $passwordHash])) {
            CLI::write("Password reset successfully for user '{$user->email}'", 'green');
        } else {
            CLI::error('Failed to reset password.');
        }
    }
}
