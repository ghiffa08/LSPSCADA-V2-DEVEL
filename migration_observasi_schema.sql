-- ===============================================================
-- SQL MIGRATION SCRIPT - OBSERVASI SYSTEM REFACTOR
-- Update tabel observasi untuk menggunakan relasi yang benar
-- dengan id_pengajuan dari tabel pengajuan_asesmen
-- ===============================================================

-- 1. BACKUP DATA EXISTING (OPTIONAL - UNTUK SAFETY)
-- CREATE TABLE observasi_backup AS SELECT * FROM observasi;
-- CREATE TABLE detail_observasi_backup AS SELECT * FROM detail_observasi;

-- ===============================================================
-- 2. UPDATE TABEL OBSERVASI
-- ===============================================================

-- Hapus foreign key constraints yang ada (jika ada)
-- ALTER TABLE observasi DROP FOREIGN KEY IF EXISTS observasi_id_pegajuan_foreign;

-- Ubah kolom id_pegajuan menjadi id_pengajuan dengan tipe yang benar
ALTER TABLE `observasi` 
CHANGE COLUMN `id_pegajuan` `id_pengajuan` int NOT NULL COMMENT 'Foreign key to pengajuan_asesmen table';

-- Tambah index untuk performa
ALTER TABLE `observasi` 
ADD INDEX `idx_observasi_pengajuan` (`id_pengajuan`),
ADD INDEX `idx_observasi_asesi_tanggal` (`id_asesi`, `tanggal_observasi`),
ADD INDEX `idx_observasi_asesor` (`id_asesor`);

-- Tambah foreign key constraint yang benar
ALTER TABLE `observasi` 
ADD CONSTRAINT `observasi_id_pengajuan_foreign` 
FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_asesmen` (`id_pengajuan`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- ===============================================================
-- 3. UPDATE TABEL DETAIL_OBSERVASI (JIKA DIPERLUKAN)
-- ===============================================================

-- Pastikan tabel detail_observasi memiliki struktur yang optimal
ALTER TABLE `detail_observasi` 
MODIFY COLUMN `kompeten` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
MODIFY COLUMN `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
MODIFY COLUMN `tanggal_observasi` date NOT NULL;

-- Tambah index untuk performa query
ALTER TABLE `detail_observasi` 
ADD INDEX `idx_detail_observasi_kuk` (`id_observasi`, `id_kuk`),
ADD INDEX `idx_detail_observasi_skema` (`id_skema`, `kompeten`),
ADD INDEX `idx_detail_tanggal` (`tanggal_observasi`);

-- ===============================================================
-- 4. MIGRASI DATA (JIKA DIPERLUKAN)
-- ===============================================================

-- Jika ada data existing yang perlu diupdate, gunakan script ini:
-- UPDATE observasi o 
-- SET o.id_pengajuan = (
--     SELECT pa.id_pengajuan 
--     FROM pengajuan_asesmen pa 
--     WHERE pa.id_asesi = o.id_asesi 
--     LIMIT 1
-- )
-- WHERE o.id_pengajuan IS NULL OR o.id_pengajuan = '';

-- ===============================================================
-- 5. OPTIMASI PERFORMA
-- ===============================================================

-- Tambah composite index untuk query yang sering digunakan
ALTER TABLE `observasi` 
ADD INDEX `idx_observasi_composite` (`id_asesor`, `tanggal_observasi`, `id_pengajuan`);

ALTER TABLE `detail_observasi` 
ADD INDEX `idx_detail_composite` (`id_observasi`, `id_skema`, `kompeten`);

-- ===============================================================
-- 6. VALIDASI DATA INTEGRITY
-- ===============================================================

-- Query untuk mengecek data yang tidak konsisten
-- SELECT 'Observasi tanpa pengajuan yang valid' as issue, COUNT(*) as count
-- FROM observasi o 
-- LEFT JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan 
-- WHERE pa.id_pengajuan IS NULL
-- UNION ALL
-- SELECT 'Detail observasi tanpa observasi parent' as issue, COUNT(*) as count
-- FROM detail_observasi do 
-- LEFT JOIN observasi o ON do.id_observasi = o.id_observasi 
-- WHERE o.id_observasi IS NULL;

-- ===============================================================
-- 7. STORED PROCEDURE UNTUK STATISTIK OBSERVASI (OPTIONAL)
-- ===============================================================

DELIMITER //

CREATE PROCEDURE GetObservasiStats(IN p_id_observasi INT)
BEGIN
    SELECT 
        o.id_observasi,
        o.tanggal_observasi,
        asesor.nama_lengkap as nama_asesor,
        asesi_user.nama_lengkap as nama_asesi,
        skema.nama_skema,
        COUNT(do.id) as total_kuk,
        SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) as kompeten_count,
        SUM(CASE WHEN do.kompeten = 'N' THEN 1 ELSE 0 END) as belum_kompeten_count,
        ROUND(
            (SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) / COUNT(do.id)) * 100, 
            2
        ) as persentase_kompeten
    FROM observasi o
    INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
    INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
    INNER JOIN users asesi_user ON asesi_user.id = asesi.id_user
    INNER JOIN users asesor ON asesor.id = o.id_asesor
    INNER JOIN skema ON skema.id_skema = pa.id_skema
    LEFT JOIN detail_observasi do ON do.id_observasi = o.id_observasi
    WHERE o.id_observasi = p_id_observasi
    GROUP BY o.id_observasi;
END //

DELIMITER ;

-- ===============================================================
-- 8. VIEW UNTUK LAPORAN OBSERVASI (OPTIONAL)
-- ===============================================================

CREATE OR REPLACE VIEW view_observasi_lengkap AS
SELECT 
    o.id_observasi,
    o.id_asesor,
    o.id_asesi,
    o.id_pengajuan,
    o.tanggal_observasi,
    o.created_at,
    o.updated_at,
    asesor.nama_lengkap as nama_asesor,
    asesi_user.nama_lengkap as nama_asesi,
    asesi_user.email as email_asesi,
    skema.id_skema,
    skema.kode_skema,
    skema.nama_skema,
    pa.status_pengajuan,
    pa.status_kompetensi,
    COUNT(do.id) as total_kuk,
    SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) as kompeten_count,
    SUM(CASE WHEN do.kompeten = 'N' THEN 1 ELSE 0 END) as belum_kompeten_count,
    CASE 
        WHEN COUNT(do.id) > 0 THEN 
            ROUND((SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) / COUNT(do.id)) * 100, 2)
        ELSE 0 
    END as persentase_kompeten
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
INNER JOIN users asesi_user ON asesi_user.id = asesi.id_user
INNER JOIN users asesor ON asesor.id = o.id_asesor
INNER JOIN skema ON skema.id_skema = pa.id_skema
LEFT JOIN detail_observasi do ON do.id_observasi = o.id_observasi
GROUP BY o.id_observasi, o.id_asesor, o.id_asesi, o.id_pengajuan, 
         o.tanggal_observasi, o.created_at, o.updated_at,
         asesor.nama_lengkap, asesi_user.nama_lengkap, asesi_user.email,
         skema.id_skema, skema.kode_skema, skema.nama_skema,
         pa.status_pengajuan, pa.status_kompetensi;

-- ===============================================================
-- 9. TESTING QUERIES
-- ===============================================================

-- Test query untuk memastikan relasi berfungsi dengan baik
-- SELECT 
--     o.id_observasi,
--     o.tanggal_observasi,
--     pa.id_pengajuan,
--     skema.nama_skema,
--     asesi_user.nama_lengkap as nama_asesi
-- FROM observasi o
-- INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
-- INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
-- INNER JOIN users asesi_user ON asesi_user.id = asesi.id_user
-- INNER JOIN skema ON skema.id_skema = pa.id_skema
-- LIMIT 10;

-- ===============================================================
-- 10. ROLLBACK SCRIPT (JIKA DIPERLUKAN)
-- ===============================================================

-- Jika perlu rollback, uncomment dan jalankan:
-- ALTER TABLE `observasi` DROP FOREIGN KEY observasi_id_pengajuan_foreign;
-- ALTER TABLE `observasi` CHANGE COLUMN `id_pengajuan` `id_pegajuan` varchar(255) NOT NULL;
-- ALTER TABLE `observasi` DROP INDEX idx_observasi_pengajuan;
-- ALTER TABLE `observasi` DROP INDEX idx_observasi_asesi_tanggal;
-- ALTER TABLE `observasi` DROP INDEX idx_observasi_asesor;
-- ALTER TABLE `observasi` DROP INDEX idx_observasi_composite;

-- ===============================================================
-- SELESAI - MIGRATION COMPLETED
-- ===============================================================

-- VERIFICATION QUERIES
SELECT 'Migration completed successfully' as status;

-- Check table structure
DESCRIBE observasi;
DESCRIBE detail_observasi;

-- Check indexes
SHOW INDEX FROM observasi;
SHOW INDEX FROM detail_observasi;

-- Check foreign keys
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME IN ('observasi', 'detail_observasi')
AND REFERENCED_TABLE_NAME IS NOT NULL;
