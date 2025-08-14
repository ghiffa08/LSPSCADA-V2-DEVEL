<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OptimizeUsersIndexes extends Migration
{
    public function up()
    {
        // Add indexes for better query performance

        // Index for username searches
        if (!$this->db->indexExists('users', 'idx_users_username')) {
            $this->forge->addKey('username', false, false, 'idx_users_username');
            $this->forge->processIndexes('users');
        }

        // Index for email searches
        if (!$this->db->indexExists('users', 'idx_users_email')) {
            $this->forge->addKey('email', false, false, 'idx_users_email');
            $this->forge->processIndexes('users');
        }

        // Index for active status
        if (!$this->db->indexExists('users', 'idx_users_active')) {
            $this->forge->addKey('active', false, false, 'idx_users_active');
            $this->forge->processIndexes('users');
        }

        // Index for soft delete queries
        if (!$this->db->indexExists('users', 'idx_users_deleted_at')) {
            $this->forge->addKey('deleted_at', false, false, 'idx_users_deleted_at');
            $this->forge->processIndexes('users');
        }

        // Composite index for common queries
        if (!$this->db->indexExists('users', 'idx_users_active_deleted')) {
            $this->forge->addKey(['active', 'deleted_at'], false, false, 'idx_users_active_deleted');
            $this->forge->processIndexes('users');
        }

        // Index for created_at ordering
        if (!$this->db->indexExists('users', 'idx_users_created_at')) {
            $this->forge->addKey('created_at', false, false, 'idx_users_created_at');
            $this->forge->processIndexes('users');
        }

        // Optimize auth_groups_users table
        if (!$this->db->indexExists('auth_groups_users', 'idx_agu_user_group')) {
            $this->forge->addKey(['user_id', 'group_id'], false, false, 'idx_agu_user_group');
            $this->forge->processIndexes('auth_groups_users');
        }
    }

    public function down()
    {
        // Remove the indexes
        $indexes = [
            'users' => [
                'idx_users_username',
                'idx_users_email',
                'idx_users_active',
                'idx_users_deleted_at',
                'idx_users_active_deleted',
                'idx_users_created_at'
            ],
            'auth_groups_users' => ['idx_agu_user_group']
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                if ($this->db->indexExists($table, $index)) {
                    $this->forge->dropKey($table, $index);
                }
            }
        }
    }
}
