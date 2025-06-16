<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditLoggingTables extends Migration
{
    public function up()
    {
        // USER_ACTIVITY_LOGS TABLE - For tracking user activities
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'resource' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'resource_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'request_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'old_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'new_values' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'failed', 'warning'],
                'default' => 'success',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->addKey(['action', 'created_at']);
        $this->forge->addKey('ip_address');
        $this->forge->addKey('status');

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        }

        $this->forge->createTable('user_activity_logs', true);

        // AUTHENTICATION_ATTEMPTS TABLE - For tracking auth attempts and security
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'attempt_type' => [
                'type' => 'ENUM',
                'constraint' => ['login', 'register', 'password_reset', 'activation', 'oauth'],
                'null' => false,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['success', 'failed', 'blocked', 'suspicious'],
                'null' => false,
            ],
            'failure_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'risk_score' => [
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
            ],
            'blocked_until' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ip_address', 'created_at']);
        $this->forge->addKey(['email', 'created_at']);
        $this->forge->addKey(['attempt_type', 'status']);
        $this->forge->addKey('risk_score');

        $this->forge->createTable('authentication_attempts', true);

        // SECURITY_VIOLATIONS TABLE - For tracking security violations
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'violation_type' => [
                'type' => 'ENUM',
                'constraint' => [
                    'suspicious_login',
                    'brute_force',
                    'invalid_session',
                    'csrf_violation',
                    'rate_limit_exceeded',
                    'unauthorized_access',
                    'sql_injection_attempt',
                    'xss_attempt',
                    'file_upload_violation'
                ],
                'null' => false,
            ],
            'severity' => [
                'type' => 'ENUM',
                'constraint' => ['low', 'medium', 'high', 'critical'],
                'default' => 'medium',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'request_uri' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'violation_data' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'action_taken' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'resolved_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['ip_address', 'created_at']);
        $this->forge->addKey(['violation_type', 'severity']);
        $this->forge->addKey('user_id');

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        }

        $this->forge->createTable('security_violations', true);

        // SESSION_TRACKING TABLE - For tracking active sessions
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => false,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => false,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'login_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'last_activity' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'logout_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'logout_reason' => [
                'type' => 'ENUM',
                'constraint' => ['manual', 'timeout', 'forced', 'security'],
                'null' => true,
            ],
            'is_active' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('session_id');
        $this->forge->addKey(['user_id', 'is_active']);
        $this->forge->addKey('last_activity');

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        }

        $this->forge->createTable('session_tracking', true);

        // RATE_LIMITING TABLE - For tracking rate limits
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'identifier' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'identifier_type' => [
                'type' => 'ENUM',
                'constraint' => ['ip', 'user', 'email'],
                'null' => false,
            ],
            'action_type' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'attempts' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 1,
            ],
            'window_start' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'blocked_until' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['identifier', 'identifier_type', 'action_type']);
        $this->forge->addKey('window_start');
        $this->forge->addKey('blocked_until');

        $this->forge->createTable('rate_limiting', true);
    }

    public function down()
    {
        $this->forge->dropTable('rate_limiting', true);
        $this->forge->dropTable('session_tracking', true);
        $this->forge->dropTable('security_violations', true);
        $this->forge->dropTable('authentication_attempts', true);
        $this->forge->dropTable('user_activity_logs', true);
    }
}
