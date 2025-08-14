<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateAsesorDataToNormalizedSchema extends Migration
{
    public function up()
    {
        // Step 1: Backup existing asesor data if needed
        $this->db->query("CREATE TABLE asesor_backup AS SELECT * FROM asesor WHERE 1=0"); // Structure only

        // Step 2: Get existing data before normalization
        $existingAsesors = $this->db->query("
            SELECT id_asesor, id_user, nomor_registrasi, bidang_kompetensi 
            FROM asesor 
            WHERE bidang_kompetensi IS NOT NULL
        ")->getResultArray();

        // Step 3: Create sample skema data if doesn't exist
        $skemas = [
            ['id_skema' => 1, 'nama_skema' => 'SCADA System Operation'],
            ['id_skema' => 2, 'nama_skema' => 'Network Administration'],
            ['id_skema' => 3, 'nama_skema' => 'Database Management'],
            ['id_skema' => 4, 'nama_skema' => 'Junior Coder']
        ];

        foreach ($skemas as $skema) {
            $exists = $this->db->table('skema')->where('id_skema', $skema['id_skema'])->get()->getRow();
            if (!$exists) {
                $this->db->table('skema')->insert($skema);
            }
        }

        // Step 4: Create asesor_skema relationships based on bidang_kompetensi
        $asesorSkemaData = [];

        foreach ($existingAsesors as $asesor) {
            $bidangKompetensi = $asesor['bidang_kompetensi'];

            // Map bidang_kompetensi to skema
            $skemaId = $this->mapBidangKompetensiToSkema($bidangKompetensi);

            if ($skemaId) {
                $asesorSkemaData[] = [
                    'id_asesor' => $asesor['id_asesor'],
                    'id_skema' => $skemaId
                ];
            }
        }

        // Step 5: Insert asesor_skema relationships
        if (!empty($asesorSkemaData)) {
            $this->db->table('asesor_skema')->insertBatch($asesorSkemaData);
        }

        // Step 6: Update existing observasi table to use id_pengajuan instead of direct id_asesi
        // This requires creating pengajuan_asesmen records if they don't exist
        $this->migrateObservasiToPengajuan();

        echo "Migration completed successfully!\n";
        echo "- Migrated " . count($existingAsesors) . " asesor records\n";
        echo "- Created " . count($asesorSkemaData) . " asesor-skema relationships\n";
    }

    public function down()
    {
        // Reverse migration - restore bidang_kompetensi column

        // Add back bidang_kompetensi column
        $this->forge->addColumn('asesor', [
            'bidang_kompetensi' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'nomor_registrasi'
            ]
        ]);

        // Restore bidang_kompetensi data from asesor_skema
        $asesorSkemas = $this->db->query("
            SELECT ask.id_asesor, GROUP_CONCAT(s.nama_skema SEPARATOR ', ') as bidang_kompetensi
            FROM asesor_skema ask
            JOIN skema s ON s.id_skema = ask.id_skema
            GROUP BY ask.id_asesor
        ")->getResultArray();

        foreach ($asesorSkemas as $row) {
            $this->db->table('asesor')
                ->where('id_asesor', $row['id_asesor'])
                ->update(['bidang_kompetensi' => $row['bidang_kompetensi']]);
        }

        // Drop asesor_skema table
        $this->forge->dropTable('asesor_skema');

        echo "Rollback completed!\n";
    }

    private function mapBidangKompetensiToSkema($bidangKompetensi)
    {
        // Map existing bidang_kompetensi to skema IDs
        $mapping = [
            'SCADA System Operation' => 1,
            'Network Administration' => 2,
            'Database Management' => 3,
            'Junior Coder' => 4
        ];

        // Try exact match first
        if (isset($mapping[$bidangKompetensi])) {
            return $mapping[$bidangKompetensi];
        }

        // Try partial match
        foreach ($mapping as $pattern => $skemaId) {
            if (stripos($bidangKompetensi, $pattern) !== false) {
                return $skemaId;
            }
        }

        // Default to first skema if no match
        return 1;
    }

    private function migrateObservasiToPengajuan()
    {
        // Get existing observasi records
        $observasiRecords = $this->db->query("
            SELECT DISTINCT o.id_asesi, o.id_asesor
            FROM observasi o
            WHERE NOT EXISTS (
                SELECT 1 FROM pengajuan_asesmen pa 
                WHERE pa.id_asesi = o.id_asesi 
                AND pa.id_asesor = o.id_asesor
            )
        ")->getResultArray();

        // Create pengajuan_asesmen records for existing observasi
        foreach ($observasiRecords as $record) {
            // Get asesor's first skema as default
            $asesorSkema = $this->db->query("
                SELECT id_skema FROM asesor_skema 
                WHERE id_asesor = ? LIMIT 1
            ", [$record['id_asesor']])->getRow();

            if ($asesorSkema) {
                $pengajuanData = [
                    'id_asesi' => $record['id_asesi'],
                    'id_asesor' => $record['id_asesor'],
                    'id_skema' => $asesorSkema->id_skema,
                    'status_pengajuan' => 'diterima',
                    'tanggal_pengajuan' => date('Y-m-d H:i:s'),
                    'status' => null
                ];

                $this->db->table('pengajuan_asesmen')->insert($pengajuanData);
            }
        }
    }
}
