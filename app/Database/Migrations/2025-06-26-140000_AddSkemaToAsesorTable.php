<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSkemaToAsesorTable extends Migration
{
    public function up()
    {
        // Add id_skema column to asesor table
        $this->forge->addColumn('asesor', [
            'id_skema' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'after' => 'nomor_registrasi'
            ]
        ]);

        // Add foreign key constraint
        $this->forge->addForeignKey('id_skema', 'skema', 'id_skema', 'CASCADE', 'SET NULL');

        // Migrate data from asesor_skema table if it exists
        $tables = $this->db->listTables();
        if (in_array('asesor_skema', $tables)) {
            // Get first skema for each asesor from the junction table
            $this->db->query("
                UPDATE asesor a
                JOIN (
                    SELECT id_asesor, MIN(id_skema) as id_skema
                    FROM asesor_skema
                    GROUP BY id_asesor
                ) x ON a.id_asesor = x.id_asesor
                SET a.id_skema = x.id_skema
            ");

            // Drop the junction table since we no longer need it
            $this->forge->dropTable('asesor_skema', true);
        }
    }

    public function down()
    {
        // Recreate junction table
        $this->forge->addField([
            'id_asesor' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'id_skema' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
        ]);

        $this->forge->addKey(['id_asesor', 'id_skema'], true);
        $this->forge->addForeignKey('id_asesor', 'asesor', 'id_asesor', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_skema', 'skema', 'id_skema', 'CASCADE', 'CASCADE');
        $this->forge->createTable('asesor_skema');

        // Migrate data back to junction table
        $asesors = $this->db->table('asesor')
            ->select('id_asesor, id_skema')
            ->where('id_skema IS NOT NULL')
            ->get()
            ->getResultArray();

        $batch = [];
        foreach ($asesors as $asesor) {
            $batch[] = [
                'id_asesor' => $asesor['id_asesor'],
                'id_skema' => $asesor['id_skema']
            ];
        }

        if (!empty($batch)) {
            $this->db->table('asesor_skema')->insertBatch($batch);
        }

        // Remove the id_skema column from asesor table
        $this->forge->dropColumn('asesor', 'id_skema');
    }
}
