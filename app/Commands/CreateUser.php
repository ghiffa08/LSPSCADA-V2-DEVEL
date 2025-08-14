<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserMythModel;
use App\Models\GroupUserModel;
use Myth\Auth\Models\GroupModel;
use Myth\Auth\Entities\User;
use Myth\Auth\Models\UserModel;

class CreateUser extends BaseCommand
{
    protected $group = 'auth';
    protected $name = 'auth:create-user';
    protected $description = 'Create a new user with specified role';

    protected $usage = 'auth:create-user [options]';
    protected $options = [
        '--role' => 'User role (Admin, Asesor, Asesi)',
        '--email' => 'User email address',
        '--username' => 'Username',
        '--name' => 'Full name',
        '--password' => 'Password (optional, will be prompted if not provided)',
        '--activate' => 'Activate user immediately (default: true)',
    ];

    public function run(array $params)
    {
        $userModel = new UserMythModel();
        $groupModel = new GroupModel();
        $groupUserModel = new GroupUserModel();

        // Get role
        $role = CLI::getOption('role') ?? CLI::prompt('User role (Admin/Asesor/Asesi)', ['Admin', 'Asesor', 'Asesi']);

        // Validate role
        $validRoles = ['Admin', 'Asesor', 'Asesi'];
        if (!in_array($role, $validRoles)) {
            CLI::error('Invalid role. Must be one of: ' . implode(', ', $validRoles));
            return;
        }

        // Check if group exists
        $group = $groupModel->where('name', $role)->first();
        if (!$group) {
            CLI::error("Group '{$role}' not found in database.");
            return;
        }

        // Get user details
        $email = CLI::getOption('email') ?? CLI::prompt('Email');
        $username = CLI::getOption('username') ?? CLI::prompt('Username');
        $name = CLI::getOption('name') ?? CLI::prompt('Full Name');
        $password = CLI::getOption('password') ?? CLI::prompt('Password', ['required' => true]);
        $activate = CLI::getOption('activate') ?? true;

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Invalid email format.');
            return;
        }

        // Check if email already exists
        if ($userModel->where('email', $email)->first()) {
            CLI::error('Email already exists.');
            return;
        }

        // Check if username already exists
        if ($userModel->where('username', $username)->first()) {
            CLI::error('Username already exists.');
            return;
        }

        try {
            // Create user using Myth/Auth
            $users = model(UserModel::class);

            $userData = [
                'email' => $email,
                'username' => $username,
                'nama_lengkap' => $name,
                'password' => $password,
            ];

            $user = new User($userData);

            if ($activate) {
                $user->activate();
            } else {
                $user->generateActivateHash();
            }

            // Assign to group
            $users = $users->withGroup($role);

            if ($users->save($user)) {
                CLI::write('User created successfully!', 'green');
                CLI::write("Email: {$email}");
                CLI::write("Username: {$username}");
                CLI::write("Name: {$name}");
                CLI::write("Role: {$role}");
                CLI::write("Status: " . ($activate ? 'Active' : 'Pending Activation'));

                if (!$activate) {
                    CLI::write('Activation email should be sent to user.', 'yellow');
                }
            } else {
                CLI::error('Failed to create user. Errors:');
                $errors = $users->errors();
                foreach ($errors as $field => $error) {
                    CLI::error("  {$field}: {$error}");
                }
            }
        } catch (\Exception $e) {
            CLI::error('Error creating user: ' . $e->getMessage());
        }
    }
}
