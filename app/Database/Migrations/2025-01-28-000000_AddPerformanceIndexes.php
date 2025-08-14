<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Performance Optimization: Add indexes to improve query performance
 * These indexes are specifically designed to optimize the most common queries
 */
class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        $forge = \Config\Database::forge();

        // 1. Observasi table indexes
        $this->addIndexIfNotExists('observasi', [
            // Composite index for common joins
            ['id_asesor', 'id_asesi', 'tanggal_observasi'],
            // Foreign key indexes
            ['id_pengajuan'],
            ['tanggal_observasi'],
            // Covering index for DataTable queries
            ['id_asesor', 'tanggal_observasi', 'id_observasi']
        ]);

        // 2. Detail_observasi table indexes  
        $this->addIndexIfNotExists('detail_observasi', [
            // Composite index for observasi details
            ['id_observasi', 'id_skema'],
            ['id_observasi', 'id_kuk'],
            // Performance index for statistics
            ['id_observasi', 'kompeten'],
            // KUK lookup index
            ['id_kuk', 'id_skema']
        ]);

        // 3. Asesor_skema table indexes
        $this->addIndexIfNotExists('asesor_skema', [
            // Primary lookup indexes
            ['id_asesor', 'id_skema'],
            ['id_skema', 'id_asesor']
        ]);

        // 4. Pengajuan_asesmen table indexes
        $this->addIndexIfNotExists('pengajuan_asesmen', [
            // Status filtering
            ['status_pengajuan', 'id_skema'],
            ['id_skema', 'status_pengajuan'],
            // Asesi lookup
            ['id_asesi', 'status_pengajuan'],
            // Asesor assignment
            ['id_asesor', 'id_skema']
        ]);

        // 5. Skema structure indexes
        $this->addIndexIfNotExists('unit', [
            ['id_skema', 'status', 'kode_unit'],
            ['id_skema', 'kode_unit']
        ]);

        $this->addIndexIfNotExists('elemen', [
            ['id_unit', 'id_skema', 'kode_elemen'],
            ['id_skema', 'status']
        ]);

        $this->addIndexIfNotExists('kuk', [
            ['id_elemen', 'id_unit', 'id_skema'],
            ['id_skema', 'status'],
            ['kode_kuk', 'id_skema']
        ]);

        // 6. User table indexes for joins
        $this->addIndexIfNotExists('users', [
            ['active', 'nama_lengkap'],
            ['email', 'active']
        ]);

        // 7. Kelompok kerja indexes
        $this->addIndexIfNotExists('kelompok_kerja', [
            ['id_skema', 'nama_kelompok']
        ]);

        $this->addIndexIfNotExists('kelompok_unit', [
            ['id_kelompok', 'id_unit']
        ]);

        echo "Performance indexes added successfully!\n";
    }

    public function down()
    {
        // Remove the indexes
        $indexes = [
            'observasi' => ['idx_asesor_asesi_tanggal', 'idx_pengajuan', 'idx_tanggal', 'idx_covering_datatable'],
            'detail_observasi' => ['idx_observasi_skema', 'idx_observasi_kuk', 'idx_observasi_kompeten', 'idx_kuk_skema'],
            'asesor_skema' => ['idx_asesor_skema', 'idx_skema_asesor'],
            'pengajuan_asesmen' => ['idx_status_skema', 'idx_skema_status', 'idx_asesi_status', 'idx_asesor_skema'],
            'unit' => ['idx_skema_status_kode', 'idx_skema_kode'],
            'elemen' => ['idx_unit_skema_kode', 'idx_skema_status'],
            'kuk' => ['idx_elemen_unit_skema', 'idx_skema_status', 'idx_kode_skema'],
            'users' => ['idx_active_nama', 'idx_email_active'],
            'kelompok_kerja' => ['idx_skema_nama'],
            'kelompok_unit' => ['idx_kelompok_unit']
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                try {
                    \Config\Database::forge()->dropKey($table, $index);
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
            }
        }
    }

    /**
     * Add index if it doesn't already exist
     */
    private function addIndexIfNotExists(string $table, array $indexes): void
    {
        $db = \Config\Database::connect();
        $existingIndexes = $this->getTableIndexes($table);

        foreach ($indexes as $index) {
            $indexName = 'idx_' . implode('_', $index);
            
            if (!in_array($indexName, $existingIndexes)) {
                try {
                    \Config\Database::forge()->addKey($index, false, false, $indexName);
                    echo "Added index {$indexName} to table {$table}\n";
                } catch (\Exception $e) {
                    echo "Warning: Could not add index {$indexName} to table {$table}: " . $e->getMessage() . "\n";
                }
            } else {
                echo "Index {$indexName} already exists on table {$table}\n";
            }
        }
    }

    /**
     * Get existing indexes for a table
     */
    private function getTableIndexes(string $table): array
    {
        $db = \Config\Database::connect();
        
        try {
            $query = $db->query("SHOW INDEX FROM {$table}");
            $result = $query->getResultArray();
            
            return array_column($result, 'Key_name');
        } catch (\Exception $e) {
            return [];
        }
    }
}
