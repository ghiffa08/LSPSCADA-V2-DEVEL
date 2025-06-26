-- ===============================================================
-- SIMPLE MIGRATION SCRIPT - OBSERVASI SYSTEM
-- Script sederhana untuk update schema observasi
-- ===============================================================

-- STEP 1: BACKUP DATA (SAFETY FIRST)
CREATE TABLE observasi_backup AS SELECT * FROM observasi;
CREATE TABLE detail_observasi_backup AS SELECT * FROM detail_observasi;

-- STEP 2: DISABLE FOREIGN KEY CHECKS TEMPORARILY
SET FOREIGN_KEY_CHECKS = 0;

-- STEP 3: UPDATE COLUMN TYPE FROM VARCHAR TO INT
-- Ganti id_pegajuan (varchar) menjadi id_pengajuan (int)
ALTER TABLE `observasi` 
DROP COLUMN `id_pegajuan`;

ALTER TABLE `observasi` 
ADD COLUMN `id_pengajuan` int NOT NULL AFTER `id_asesi`;

-- STEP 4: POPULATE id_pengajuan DENGAN DATA YANG SESUAI
-- Untuk data existing, kita map berdasarkan id_asesi
UPDATE observasi o 
SET o.id_pengajuan = (
    SELECT pa.id_pengajuan 
    FROM pengajuan_asesmen pa 
    WHERE pa.id_asesi = o.id_asesi 
    ORDER BY pa.tanggal_pengajuan DESC 
    LIMIT 1
);

-- STEP 5: ADD FOREIGN KEY CONSTRAINT
ALTER TABLE `observasi` 
ADD CONSTRAINT `fk_observasi_pengajuan` 
FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_asesmen` (`id_pengajuan`) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- STEP 6: ADD INDEXES FOR PERFORMANCE
ALTER TABLE `observasi` 
ADD INDEX `idx_pengajuan` (`id_pengajuan`),
ADD INDEX `idx_asesi_tanggal` (`id_asesi`, `tanggal_observasi`);

-- STEP 7: RE-ENABLE FOREIGN KEY CHECKS
SET FOREIGN_KEY_CHECKS = 1;

-- STEP 8: VERIFY STRUCTURE
DESCRIBE observasi;

-- STEP 9: VERIFY DATA
SELECT COUNT(*) as total_observasi FROM observasi;
SELECT COUNT(*) as observasi_with_valid_pengajuan 
FROM observasi o 
INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan;

SELECT 'Migration completed successfully!' as status;
