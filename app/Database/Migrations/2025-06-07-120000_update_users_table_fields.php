<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUsersTableFields extends Migration
{
    public function up()
    {
        // Add nama_lengkap column
        $this->forge->addColumn('users', [
            'nama_lengkap' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'after' => 'username'
            ]
        ]);

        // Copy data from fullname to nama_lengkap
        $this->db->query("UPDATE users SET nama_lengkap = fullname WHERE fullname IS NOT NULL");

        // Drop fullname and no_telp columns
        $this->forge->dropColumn('users', ['fullname', 'no_telp']);
    }

    public function down()
    {
        // Add back fullname and no_telp columns
        $this->forge->addColumn('users', [
            'fullname' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'after' => 'username'
            ],
            'no_telp' => [
                'type' => 'varchar',
                'constraint' => 255,
                'null' => true,
                'after' => 'nama_lengkap'
            ]
        ]);

        // Copy data back from nama_lengkap to fullname
        $this->db->query("UPDATE users SET fullname = nama_lengkap WHERE nama_lengkap IS NOT NULL");

        // Drop nama_lengkap column
        $this->forge->dropColumn('users', 'nama_lengkap');
    }
}
