<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAsesorSkemaRelation extends Migration
{
    public function up()
    {
        // First create a backup of the asesor table to preserve bidang_kompetensi data
        $this->forge->addField([
            'id_asesor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'nomor_registrasi' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'bidang_kompetensi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
        ]);
        $this->forge->createTable('asesor_backup');

        // Copy data to backup table
        $this->db->query("INSERT INTO asesor_backup SELECT * FROM asesor");

        // Create asesor_skema table for many-to-many relationship
        $this->forge->addField([
            'id_asesor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => false,
            ],
            'id_skema' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => false,
            ],
        ]);

        $this->forge->addKey(['id_asesor', 'id_skema'], true); // Primary key
        $this->forge->addForeignKey('id_asesor', 'asesor', 'id_asesor', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_skema', 'skema', 'id_skema', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asesor_skema');

        // Migrate data from bidang_kompetensi to asesor_skema relation
        $this->db->query("
            INSERT INTO asesor_skema (id_asesor, id_skema)
            SELECT a.id_asesor, s.id_skema
            FROM asesor a
            JOIN skema s ON s.nama_skema = a.bidang_kompetensi
            WHERE a.bidang_kompetensi IS NOT NULL");

        // Now remove the bidang_kompetensi column from asesor table
        $this->forge->dropColumn('asesor', 'bidang_kompetensi');
    }

    public function down()
    {
        // Add back the bidang_kompetensi column
        $this->forge->addColumn('asesor', [
            'bidang_kompetensi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'nomor_registrasi'
            ],
        ]);

        // Restore data from backup
        $this->db->query("
            UPDATE asesor a 
            JOIN asesor_backup ab ON a.id_asesor = ab.id_asesor 
            SET a.bidang_kompetensi = ab.bidang_kompetensi");

        // Drop the bridging table
        $this->forge->dropTable('asesor_skema');

        // Drop the backup table
        $this->forge->dropTable('asesor_backup');
    }
}
