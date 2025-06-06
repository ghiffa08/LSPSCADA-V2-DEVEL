<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixAuthForeignKeys extends Migration
{
    public function up()
    {
        // Drop existing foreign key constraints that reference users.id
        if ($this->db->DBDriver !== 'SQLite3') {
            try {
                $this->forge->dropForeignKey('auth_tokens', 'auth_tokens_user_id_foreign');
            } catch (Exception $e) {
                // Constraint might not exist or have different name
            }

            try {
                $this->forge->dropForeignKey('auth_groups_users', 'auth_groups_users_user_id_foreign');
            } catch (Exception $e) {
                // Constraint might not exist or have different name
            }

            try {
                $this->forge->dropForeignKey('auth_users_permissions', 'auth_users_permissions_user_id_foreign');
            } catch (Exception $e) {
                // Constraint might not exist or have different name
            }
        }

        // Clean up any orphaned records in auth tables that reference non-existent user IDs
        // Update auth_groups_users to reference id_user correctly
        $this->db->query("DELETE FROM auth_groups_users WHERE user_id NOT IN (SELECT id_user FROM users)");

        // Update auth_tokens to reference id_user correctly
        $this->db->query("DELETE FROM auth_tokens WHERE user_id NOT IN (SELECT id_user FROM users)");

        // Update auth_users_permissions to reference id_user correctly
        $this->db->query("DELETE FROM auth_users_permissions WHERE user_id NOT IN (SELECT id_user FROM users)");

        // Add back foreign key constraints with correct reference to users.id_user
        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->addForeignKey('user_id', 'users', 'id_user', 'CASCADE', 'CASCADE', 'auth_tokens_user_id_foreign');
            $this->forge->processIndexes('auth_tokens');

            $this->forge->addForeignKey('user_id', 'users', 'id_user', 'CASCADE', 'CASCADE', 'auth_groups_users_user_id_foreign');
            $this->forge->processIndexes('auth_groups_users');

            $this->forge->addForeignKey('user_id', 'users', 'id_user', 'CASCADE', 'CASCADE', 'auth_users_permissions_user_id_foreign');
            $this->forge->processIndexes('auth_users_permissions');
        }
    }

    public function down()
    {
        // Revert foreign key constraints back to users.id (this would fail since users.id doesn't exist)
        // This is essentially irreversible since the old structure is gone
        if ($this->db->DBDriver !== 'SQLite3') {
            try {
                $this->forge->dropForeignKey('auth_tokens', 'auth_tokens_user_id_foreign');
                $this->forge->dropForeignKey('auth_groups_users', 'auth_groups_users_user_id_foreign');
                $this->forge->dropForeignKey('auth_users_permissions', 'auth_users_permissions_user_id_foreign');
            } catch (Exception $e) {
                // Constraints might not exist
            }
        }
    }
}
