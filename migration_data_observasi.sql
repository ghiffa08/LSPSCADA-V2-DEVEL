-- ===============================================================
-- DATA MIGRATION SCRIPT - OBSERVASI SYSTEM
-- Script untuk migrasi data existing dari id_pegajuan ke id_pengajuan
-- ===============================================================

USE lsp_scada_app_devel;

-- ===============================================================
-- 1. ANALISIS DATA EXISTING
-- ===============================================================

-- Cek struktur tabel current
SELECT 'Struktur tabel observasi saat ini:' as info;
DESCRIBE observasi;

-- Cek data existing di tabel observasi
SELECT 'Total data observasi existing:' as info, COUNT(*) as total FROM observasi;

-- Cek data di tabel pengajuan_asesmen
SELECT 'Total data pengajuan_asesmen:' as info, COUNT(*) as total FROM pengajuan_asesmen;

-- ===============================================================
-- 2. BACKUP DATA SEBELUM MIGRASI
-- ===============================================================

-- Backup tabel observasi
CREATE TABLE IF NOT EXISTS observasi_backup_20250623 AS 
SELECT * FROM observasi;

-- Backup tabel detail_observasi  
CREATE TABLE IF NOT EXISTS detail_observasi_backup_20250623 AS 
SELECT * FROM detail_observasi;

SELECT 'Data backup completed' as status;

-- ===============================================================
-- 3. ANALISIS RELASI DATA
-- ===============================================================

-- Cek apakah ada data observasi yang tidak memiliki pengajuan yang sesuai
SELECT 
    'Observasi tanpa pengajuan yang sesuai' as issue,
    COUNT(*) as count
FROM observasi o
LEFT JOIN pengajuan_asesmen pa ON pa.id_asesi = o.id_asesi
WHERE pa.id_pengajuan IS NULL;

-- Cek data yang bisa dimap
SELECT 
    'Observasi yang bisa dimap ke pengajuan' as issue,
    COUNT(*) as count
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON pa.id_asesi = o.id_asesi;

-- ===============================================================
-- 4. TEMPORARY COLUMN UNTUK MIGRASI
-- ===============================================================

-- Tambah kolom temporary untuk menyimpan id_pengajuan yang benar
ALTER TABLE observasi ADD COLUMN temp_id_pengajuan INT;

-- Update temporary column dengan id_pengajuan yang sesuai
UPDATE observasi o 
SET temp_id_pengajuan = (
    SELECT pa.id_pengajuan 
    FROM pengajuan_asesmen pa 
    WHERE pa.id_asesi = o.id_asesi 
    ORDER BY pa.tanggal_pengajuan DESC 
    LIMIT 1
)
WHERE o.temp_id_pengajuan IS NULL;

-- Cek hasil mapping
SELECT 
    'Mapping berhasil' as status,
    COUNT(*) as count
FROM observasi 
WHERE temp_id_pengajuan IS NOT NULL;

SELECT 
    'Mapping gagal' as status,
    COUNT(*) as count
FROM observasi 
WHERE temp_id_pengajuan IS NULL;

-- ===============================================================
-- 5. HANDLE DATA YANG TIDAK BISA DIMAP
-- ===============================================================

-- Untuk data yang tidak bisa dimap, kita buat entry pengajuan_asesmen dummy
-- atau gunakan default values

-- Cek data yang tidak bisa dimap
SELECT 
    o.id_observasi,
    o.id_asesi,
    o.tanggal_observasi,
    'Tidak ada pengajuan yang sesuai' as issue
FROM observasi o
WHERE temp_id_pengajuan IS NULL;

-- Untuk data yang tidak bisa dimap, kita bisa:
-- 1. Hapus data tersebut (jika data tidak penting)
-- 2. Buat pengajuan dummy
-- 3. Set ke pengajuan default

-- Option 1: Hapus data yang tidak bisa dimap (HATI-HATI!)
-- DELETE FROM detail_observasi WHERE id_observasi IN (
--     SELECT id_observasi FROM observasi WHERE temp_id_pengajuan IS NULL
-- );
-- DELETE FROM observasi WHERE temp_id_pengajuan IS NULL;

-- Option 2: Buat pengajuan dummy untuk data orphan
INSERT INTO pengajuan_asesmen (id_pengajuan, id_asesi, id_skema, status_pengajuan, tanggal_pengajuan)
SELECT 
    NULL as id_pengajuan,
    o.id_asesi,
    1 as id_skema, -- Default skema, sesuaikan dengan kebutuhan
    'pending' as status_pengajuan,
    o.tanggal_observasi as tanggal_pengajuan
FROM observasi o
WHERE temp_id_pengajuan IS NULL
AND NOT EXISTS (
    SELECT 1 FROM pengajuan_asesmen pa WHERE pa.id_asesi = o.id_asesi
);

-- Update temp_id_pengajuan untuk data yang baru dibuat
UPDATE observasi o 
SET temp_id_pengajuan = (
    SELECT pa.id_pengajuan 
    FROM pengajuan_asesmen pa 
    WHERE pa.id_asesi = o.id_asesi 
    ORDER BY pa.id_pengajuan DESC 
    LIMIT 1
)
WHERE temp_id_pengajuan IS NULL;

-- ===============================================================
-- 6. EKSEKUSI MIGRASI SCHEMA
-- ===============================================================

-- Hapus foreign key yang ada (jika ada)
SET FOREIGN_KEY_CHECKS = 0;

-- Rename kolom dari id_pegajuan ke id_pengajuan_old (backup)
ALTER TABLE observasi CHANGE COLUMN id_pegajuan id_pengajuan_old VARCHAR(255);

-- Buat kolom id_pengajuan yang baru dengan tipe yang benar
ALTER TABLE observasi ADD COLUMN id_pengajuan INT NOT NULL AFTER id_asesi;

-- Copy data dari temp column ke kolom yang baru
UPDATE observasi SET id_pengajuan = temp_id_pengajuan;

-- Hapus temp column
ALTER TABLE observasi DROP COLUMN temp_id_pengajuan;

-- Tambah index dan foreign key
ALTER TABLE observasi 
ADD INDEX idx_observasi_pengajuan (id_pengajuan);

ALTER TABLE observasi 
ADD CONSTRAINT observasi_id_pengajuan_foreign 
FOREIGN KEY (id_pengajuan) REFERENCES pengajuan_asesmen (id_pengajuan) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Enable foreign key checks kembali
SET FOREIGN_KEY_CHECKS = 1;

-- ===============================================================
-- 7. VALIDASI HASIL MIGRASI
-- ===============================================================

-- Cek struktur tabel setelah migrasi
SELECT 'Struktur tabel observasi setelah migrasi:' as info;
DESCRIBE observasi;

-- Cek data yang berhasil dimigrasi
SELECT 
    'Data observasi setelah migrasi' as info,
    COUNT(*) as total,
    MIN(id_pengajuan) as min_id_pengajuan,
    MAX(id_pengajuan) as max_id_pengajuan
FROM observasi;

-- Test join dengan tabel pengajuan_asesmen
SELECT 
    'Test join dengan pengajuan_asesmen' as info,
    COUNT(*) as total_valid_joins
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan;

-- Cek jika ada data yang tidak valid
SELECT 
    'Data observasi dengan relasi invalid' as info,
    COUNT(*) as count
FROM observasi o
LEFT JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
WHERE pa.id_pengajuan IS NULL;

-- ===============================================================
-- 8. CLEANUP (OPSIONAL)
-- ===============================================================

-- Setelah memastikan migrasi berhasil, hapus kolom backup
-- ALTER TABLE observasi DROP COLUMN id_pengajuan_old;

-- ===============================================================
-- 9. TESTING QUERIES
-- ===============================================================

-- Test query kompleks dengan relasi baru
SELECT 
    o.id_observasi,
    o.tanggal_observasi,
    pa.id_pengajuan,
    pa.status_pengajuan,
    asesi_user.nama_lengkap as nama_asesi,
    skema.nama_skema,
    COUNT(do.id) as total_kuk,
    SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) as kompeten_count
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
INNER JOIN users asesi_user ON asesi_user.id = asesi.id_user
INNER JOIN skema ON skema.id_skema = pa.id_skema
LEFT JOIN detail_observasi do ON do.id_observasi = o.id_observasi
GROUP BY o.id_observasi
LIMIT 5;

-- ===============================================================
-- 10. PERFORMANCE TEST
-- ===============================================================

-- Test performa query sebelum dan sesudah
EXPLAIN SELECT 
    o.id_observasi,
    pa.status_pengajuan,
    asesi_user.nama_lengkap
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
INNER JOIN users asesi_user ON asesi_user.id = asesi.id_user
WHERE o.tanggal_observasi >= '2025-01-01';

SELECT 'Migrasi data selesai!' as status;
