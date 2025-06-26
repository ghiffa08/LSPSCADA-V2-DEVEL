<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class OptimizeObservasiTables extends Migration
{
    public function up()
    {
        // ===================================================
        // OPTIMASI TABEL OBSERVASI
        // ===================================================

        // 1. Pastikan kolom-kolom utama memiliki index yang optimal
        $this->forge->addIndex('observasi', 'id_asesor', 'idx_observasi_asesor');
        $this->forge->addIndex('observasi', 'id_asesi', 'idx_observasi_asesi');
        $this->forge->addIndex('observasi', 'id_pengajuan', 'idx_observasi_pengajuan');
        $this->forge->addIndex('observasi', 'tanggal_observasi', 'idx_observasi_tanggal');

        // 2. Composite index untuk query yang sering digunakan
        $this->forge->addIndex('observasi', ['id_asesor', 'tanggal_observasi'], 'idx_observasi_asesor_tanggal');
        $this->forge->addIndex('observasi', ['id_asesi', 'tanggal_observasi'], 'idx_observasi_asesi_tanggal');
        $this->forge->addIndex('observasi', ['id_pengajuan', 'id_asesor'], 'idx_observasi_pengajuan_asesor');

        // ===================================================
        // OPTIMASI TABEL DETAIL_OBSERVASI
        // ===================================================

        // 1. Index untuk kolom-kolom yang sering digunakan dalam WHERE clause
        $this->forge->addIndex('detail_observasi', 'id_observasi', 'idx_detail_observasi');
        $this->forge->addIndex('detail_observasi', 'id_skema', 'idx_detail_skema');
        $this->forge->addIndex('detail_observasi', 'id_kuk', 'idx_detail_kuk');
        $this->forge->addIndex('detail_observasi', 'kompeten', 'idx_detail_kompeten');
        $this->forge->addIndex('detail_observasi', 'tanggal_observasi', 'idx_detail_tanggal');

        // 2. Composite index untuk JOIN dan filter yang kompleks
        $this->forge->addIndex('detail_observasi', ['id_observasi', 'id_kuk'], 'idx_detail_observasi_kuk');
        $this->forge->addIndex('detail_observasi', ['id_skema', 'kompeten'], 'idx_detail_skema_kompeten');
        $this->forge->addIndex('detail_observasi', ['id_observasi', 'id_skema', 'kompeten'], 'idx_detail_composite');

        // ===================================================
        // TAMBAH KOLOM UNTUK CACHING DAN OPTIMASI
        // ===================================================

        // Tambah kolom untuk caching progress
        if (!$this->db->fieldExists('total_kuk', 'observasi')) {
            $this->forge->addColumn('observasi', [
                'total_kuk' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                    'comment' => 'Total KUK dalam observasi ini'
                ]
            ]);
        }

        if (!$this->db->fieldExists('kompeten_count', 'observasi')) {
            $this->forge->addColumn('observasi', [
                'kompeten_count' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 0,
                    'comment' => 'Jumlah KUK yang kompeten'
                ]
            ]);
        }

        if (!$this->db->fieldExists('progress_percentage', 'observasi')) {
            $this->forge->addColumn('observasi', [
                'progress_percentage' => [
                    'type' => 'DECIMAL',
                    'constraint' => '5,2',
                    'default' => 0.00,
                    'comment' => 'Persentase progress observasi'
                ]
            ]);
        }

        if (!$this->db->fieldExists('status', 'observasi')) {
            $this->forge->addColumn('observasi', [
                'status' => [
                    'type' => 'ENUM',
                    'constraint' => ['draft', 'in_progress', 'completed', 'submitted'],
                    'default' => 'draft',
                    'comment' => 'Status observasi'
                ]
            ]);
        }

        if (!$this->db->fieldExists('created_at', 'observasi')) {
            $this->forge->addColumn('observasi', [
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Waktu pembuatan record'
                ]
            ]);
        }

        if (!$this->db->fieldExists('updated_at', 'observasi')) {
            $this->forge->addColumn('observasi', [
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'comment' => 'Waktu update terakhir'
                ]
            ]);
        }

        // ===================================================
        // TRIGGER UNTUK AUTO-UPDATE PROGRESS
        // ===================================================

        $this->db->query("
            DROP TRIGGER IF EXISTS update_observasi_progress;
        ");

        $this->db->query("
            CREATE TRIGGER update_observasi_progress
            AFTER INSERT ON detail_observasi
            FOR EACH ROW
            BEGIN
                DECLARE total_count INT DEFAULT 0;
                DECLARE kompeten_count INT DEFAULT 0;
                DECLARE progress_pct DECIMAL(5,2) DEFAULT 0.00;
                
                -- Hitung total KUK
                SELECT COUNT(*) INTO total_count
                FROM detail_observasi 
                WHERE id_observasi = NEW.id_observasi;
                
                -- Hitung KUK yang kompeten
                SELECT COUNT(*) INTO kompeten_count
                FROM detail_observasi 
                WHERE id_observasi = NEW.id_observasi 
                AND kompeten = 'Y';
                
                -- Hitung persentase
                IF total_count > 0 THEN
                    SET progress_pct = (kompeten_count / total_count) * 100;
                END IF;
                
                -- Update observasi
                UPDATE observasi 
                SET 
                    total_kuk = total_count,
                    kompeten_count = kompeten_count,
                    progress_percentage = progress_pct,
                    updated_at = NOW()
                WHERE id_observasi = NEW.id_observasi;
            END;
        ");

        $this->db->query("
            DROP TRIGGER IF EXISTS update_observasi_progress_update;
        ");

        $this->db->query("
            CREATE TRIGGER update_observasi_progress_update
            AFTER UPDATE ON detail_observasi
            FOR EACH ROW
            BEGIN
                DECLARE total_count INT DEFAULT 0;
                DECLARE kompeten_count INT DEFAULT 0;
                DECLARE progress_pct DECIMAL(5,2) DEFAULT 0.00;
                
                -- Hitung total KUK
                SELECT COUNT(*) INTO total_count
                FROM detail_observasi 
                WHERE id_observasi = NEW.id_observasi;
                
                -- Hitung KUK yang kompeten
                SELECT COUNT(*) INTO kompeten_count
                FROM detail_observasi 
                WHERE id_observasi = NEW.id_observasi 
                AND kompeten = 'Y';
                
                -- Hitung persentase
                IF total_count > 0 THEN
                    SET progress_pct = (kompeten_count / total_count) * 100;
                END IF;
                
                -- Update observasi
                UPDATE observasi 
                SET 
                    total_kuk = total_count,
                    kompeten_count = kompeten_count,
                    progress_percentage = progress_pct,
                    updated_at = NOW()
                WHERE id_observasi = NEW.id_observasi;
            END;
        ");

        // ===================================================
        // STORED PROCEDURES UNTUK OPTIMASI QUERY
        // ===================================================

        $this->db->query("
            DROP PROCEDURE IF EXISTS GetObservasiDetailOptimized;
        ");

        $this->db->query("
            CREATE PROCEDURE GetObservasiDetailOptimized(
                IN p_id_skema INT,
                IN p_id_asesi VARCHAR(50)
            )
            BEGIN
                -- Query yang dioptimalkan untuk load observasi
                SELECT 
                    s.id_skema,
                    s.kode_skema,
                    s.nama_skema,
                    s.jenis_skema,
                    kk.id_kelompok,
                    kk.nama_kelompok,
                    u.id_unit,
                    u.kode_unit,
                    u.nama_unit,
                    e.id_elemen,
                    e.kode_elemen,
                    e.nama_elemen,
                    k.id_kuk,
                    k.kode_kuk,
                    k.nama_kuk AS kriteria_unjuk_kerja,
                    COALESCE(do.kompeten, '') AS kompeten,
                    COALESCE(do.keterangan, '') AS keterangan
                FROM skema s
                INNER JOIN kelompok_kerja kk ON kk.id_skema = s.id_skema
                INNER JOIN kelompok_unit ku ON ku.id_kelompok = kk.id_kelompok
                INNER JOIN unit u ON u.id_unit = ku.id_unit AND u.id_skema = s.id_skema
                LEFT JOIN elemen e ON e.id_unit = u.id_unit AND e.id_skema = s.id_skema
                LEFT JOIN kuk k ON k.id_elemen = e.id_elemen AND k.id_unit = u.id_unit AND k.id_skema = s.id_skema
                LEFT JOIN observasi o ON o.id_asesi = p_id_asesi
                LEFT JOIN detail_observasi do ON do.id_observasi = o.id_observasi 
                    AND do.id_skema = s.id_skema 
                    AND do.id_kuk = k.id_kuk
                WHERE s.id_skema = p_id_skema
                    AND s.status = 'Y'
                    AND u.status = 'Y'
                ORDER BY u.kode_unit ASC, e.kode_elemen ASC, k.kode_kuk ASC;
            END;
        ");
    }

    public function down()
    {
        // Drop triggers
        $this->db->query("DROP TRIGGER IF EXISTS update_observasi_progress");
        $this->db->query("DROP TRIGGER IF EXISTS update_observasi_progress_update");

        // Drop stored procedures
        $this->db->query("DROP PROCEDURE IF EXISTS GetObservasiDetailOptimized");

        // Drop added columns
        $columns = ['total_kuk', 'kompeten_count', 'progress_percentage', 'status', 'created_at', 'updated_at'];
        foreach ($columns as $column) {
            if ($this->db->fieldExists($column, 'observasi')) {
                $this->forge->dropColumn('observasi', $column);
            }
        }

        // Drop indexes (CodeIgniter akan menangani ini secara otomatis saat rollback)
    }
}
