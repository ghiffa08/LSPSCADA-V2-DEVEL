<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateQueueTables extends Migration
{
    public function up()
    {
        // Create queued jobs table
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'queue' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'attempts' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 0,
            ],
            'reserved_at' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'available_at' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['queue', 'reserved_at']);
        $this->forge->createTable('queued_jobs');

        // Create failed jobs table
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'connection' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'queue' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'exception' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'failed_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
                'default' => date('Y-m-d H:i:s'),
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('failed_jobs');
    }

    public function down()
    {
        $this->forge->dropTable('queued_jobs');
        $this->forge->dropTable('failed_jobs');
    }
}
