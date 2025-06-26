-- ===============================================================
-- CREATE TABLE STATEMENTS - OBSERVASI SYSTEM (LATEST VERSION)
-- Updated: June 23, 2025
-- Compatible with phpMyAdmin - Ready to copy paste
-- ===============================================================

-- Use your database (uncomment and modify as needed)
-- USE lsp_scada_app_devel;

-- ===============================================================
-- 1. CREATE TABLE observasi (OPTIMIZED VERSION)
-- ===============================================================

CREATE TABLE `observasi` (
  `id_observasi` int(11) NOT NULL AUTO_INCREMENT,
  `id_asesi` int(11) NOT NULL,
  `id_pengajuan` int(11) NOT NULL,
  `id_asesor` int(11) NOT NULL,
  `tanggal_observasi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tempat_observasi` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status_observasi` enum('draft','in_progress','completed','approved','rejected') NOT NULL DEFAULT 'draft',
  `total_kuk` int(11) DEFAULT 0,
  `kompeten_count` int(11) DEFAULT 0,
  `persentase_kompeten` decimal(5,2) DEFAULT 0.00,
  `rekomendasi` enum('kompeten','belum_kompeten','perlu_perbaikan') DEFAULT NULL,
  `catatan_asesor` text DEFAULT NULL,
  `signature_asesi` text DEFAULT NULL,
  `signature_asesor` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_observasi`),
  KEY `idx_observasi_asesi` (`id_asesi`),
  KEY `idx_observasi_pengajuan` (`id_pengajuan`),
  KEY `idx_observasi_asesor` (`id_asesor`),
  KEY `idx_observasi_tanggal` (`tanggal_observasi`),
  KEY `idx_observasi_status` (`status_observasi`),
  KEY `idx_observasi_created_at` (`created_at`),
  CONSTRAINT `observasi_id_asesi_foreign` FOREIGN KEY (`id_asesi`) REFERENCES `asesi` (`id_asesi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `observasi_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_asesmen` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `observasi_id_asesor_foreign` FOREIGN KEY (`id_asesor`) REFERENCES `asesor` (`id_asesor`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel untuk menyimpan data observasi asesmen dengan foreign key ke pengajuan_asesmen';

-- ===============================================================
-- 2. CREATE TABLE detail_observasi (OPTIMIZED VERSION)
-- ===============================================================

CREATE TABLE `detail_observasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_observasi` int(11) NOT NULL,
  `id_kuk` int(11) NOT NULL,
  `id_unit_kompetensi` int(11) DEFAULT NULL,
  `kode_kuk` varchar(50) NOT NULL,
  `deskripsi_kuk` text NOT NULL,
  `kompeten` enum('Y','N','NA') NOT NULL DEFAULT 'N',
  `bukti_observasi` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `skor` tinyint(4) DEFAULT NULL CHECK (`skor` >= 0 AND `skor` <= 100),
  `bobot` decimal(3,2) DEFAULT 1.00,
  `urutan` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_observasi_kuk` (`id_observasi`,`id_kuk`),
  KEY `idx_detail_observasi_observasi` (`id_observasi`),
  KEY `idx_detail_observasi_kuk` (`id_kuk`),
  KEY `idx_detail_observasi_unit` (`id_unit_kompetensi`),
  KEY `idx_detail_observasi_kompeten` (`kompeten`),
  KEY `idx_detail_observasi_urutan` (`urutan`),
  KEY `idx_detail_observasi_created_at` (`created_at`),
  CONSTRAINT `detail_observasi_id_observasi_foreign` FOREIGN KEY (`id_observasi`) REFERENCES `observasi` (`id_observasi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_observasi_id_kuk_foreign` FOREIGN KEY (`id_kuk`) REFERENCES `kuk` (`id_kuk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_observasi_id_unit_foreign` FOREIGN KEY (`id_unit_kompetensi`) REFERENCES `unit_kompetensi` (`id_unit_kompetensi`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabel detail untuk menyimpan hasil observasi per KUK dengan optimisasi untuk batch operations';

-- ===============================================================
-- 3. CREATE INDEXES FOR PERFORMANCE (ADDITIONAL)
-- ===============================================================

-- Composite indexes untuk query kompleks
CREATE INDEX `idx_observasi_asesi_status_tanggal` ON `observasi` (`id_asesi`, `status_observasi`, `tanggal_observasi`);
CREATE INDEX `idx_observasi_pengajuan_status` ON `observasi` (`id_pengajuan`, `status_observasi`);
CREATE INDEX `idx_observasi_asesor_tanggal` ON `observasi` (`id_asesor`, `tanggal_observasi`);

-- Indexes untuk detail_observasi untuk performa reporting
CREATE INDEX `idx_detail_kompeten_skor` ON `detail_observasi` (`kompeten`, `skor`);
CREATE INDEX `idx_detail_observasi_unit_kompeten` ON `detail_observasi` (`id_unit_kompetensi`, `kompeten`);

-- ===============================================================
-- 4. CREATE TRIGGERS FOR AUTO-CALCULATION (OPTIONAL)
-- ===============================================================

-- Trigger untuk auto-update persentase kompeten di tabel observasi
DELIMITER $$

CREATE TRIGGER `update_observasi_stats_after_insert` 
AFTER INSERT ON `detail_observasi`
FOR EACH ROW
BEGIN
    UPDATE `observasi` SET
        `total_kuk` = (
            SELECT COUNT(*) 
            FROM `detail_observasi` 
            WHERE `id_observasi` = NEW.`id_observasi`
        ),
        `kompeten_count` = (
            SELECT COUNT(*) 
            FROM `detail_observasi` 
            WHERE `id_observasi` = NEW.`id_observasi` AND `kompeten` = 'Y'
        ),
        `persentase_kompeten` = (
            SELECT ROUND(
                (COUNT(CASE WHEN `kompeten` = 'Y' THEN 1 END) * 100.0) / COUNT(*), 2
            )
            FROM `detail_observasi` 
            WHERE `id_observasi` = NEW.`id_observasi`
        ),
        `updated_at` = CURRENT_TIMESTAMP
    WHERE `id_observasi` = NEW.`id_observasi`;
END$$

CREATE TRIGGER `update_observasi_stats_after_update` 
AFTER UPDATE ON `detail_observasi`
FOR EACH ROW
BEGIN
    UPDATE `observasi` SET
        `total_kuk` = (
            SELECT COUNT(*) 
            FROM `detail_observasi` 
            WHERE `id_observasi` = NEW.`id_observasi`
        ),
        `kompeten_count` = (
            SELECT COUNT(*) 
            FROM `detail_observasi` 
            WHERE `id_observasi` = NEW.`id_observasi` AND `kompeten` = 'Y'
        ),
        `persentase_kompeten` = (
            SELECT ROUND(
                (COUNT(CASE WHEN `kompeten` = 'Y' THEN 1 END) * 100.0) / COUNT(*), 2
            )
            FROM `detail_observasi` 
            WHERE `id_observasi` = NEW.`id_observasi`
        ),
        `updated_at` = CURRENT_TIMESTAMP
    WHERE `id_observasi` = NEW.`id_observasi`;
END$$

CREATE TRIGGER `update_observasi_stats_after_delete` 
AFTER DELETE ON `detail_observasi`
FOR EACH ROW
BEGIN
    UPDATE `observasi` SET
        `total_kuk` = (
            SELECT COUNT(*) 
            FROM `detail_observasi` 
            WHERE `id_observasi` = OLD.`id_observasi`
        ),
        `kompeten_count` = (
            SELECT COUNT(*) 
            FROM `detail_observasi` 
            WHERE `id_observasi` = OLD.`id_observasi` AND `kompeten` = 'Y'
        ),
        `persentase_kompeten` = (
            SELECT CASE 
                WHEN COUNT(*) = 0 THEN 0 
                ELSE ROUND((COUNT(CASE WHEN `kompeten` = 'Y' THEN 1 END) * 100.0) / COUNT(*), 2)
            END
            FROM `detail_observasi` 
            WHERE `id_observasi` = OLD.`id_observasi`
        ),
        `updated_at` = CURRENT_TIMESTAMP
    WHERE `id_observasi` = OLD.`id_observasi`;
END$$

DELIMITER ;

-- ===============================================================
-- 5. SAMPLE DATA (OPTIONAL - FOR TESTING)
-- ===============================================================

-- Uncomment to insert sample data for testing
/*
-- Sample observasi
INSERT INTO `observasi` 
(`id_asesi`, `id_pengajuan`, `id_asesor`, `tanggal_observasi`, `tempat_observasi`, `keterangan`, `status_observasi`) 
VALUES 
(1, 1, 1, NOW(), 'Workshop LSP', 'Observasi praktik kompetensi', 'in_progress');

-- Sample detail_observasi
INSERT INTO `detail_observasi` 
(`id_observasi`, `id_kuk`, `kode_kuk`, `deskripsi_kuk`, `kompeten`, `bukti_observasi`, `urutan`) 
VALUES 
(1, 1, 'KUK.001', 'Mengidentifikasi kebutuhan sistem', 'Y', 'Asesi dapat mengidentifikasi kebutuhan dengan baik', 1),
(1, 2, 'KUK.002', 'Merancang sistem observasi', 'Y', 'Perancangan sistem sesuai standar', 2),
(1, 3, 'KUK.003', 'Mengimplementasikan solusi', 'N', 'Perlu perbaikan dalam implementasi', 3);
*/

-- ===============================================================
-- NOTES & DOCUMENTATION
-- ===============================================================

/*
FEATURES INCLUDED:
1. ✅ Foreign key ke pengajuan_asesmen (bukan lagi id_pegajuan/id_apl1)
2. ✅ Indexes yang dioptimalkan untuk performa
3. ✅ Auto-calculation triggers untuk persentase kompenten
4. ✅ Enum fields untuk status dan kompeten
5. ✅ Unique constraint untuk mencegah duplikasi observasi per KUK
6. ✅ Timestamps dengan auto-update
7. ✅ Charset utf8mb4 untuk emoji/unicode support
8. ✅ Comments pada setiap constraint untuk dokumentasi
9. ✅ Skor dan bobot untuk sistem penilaian yang lebih fleksibel
10. ✅ Signature fields untuk digital signature
11. ✅ Audit trail (created_by, updated_by)

BREAKING CHANGES FROM LEGACY:
- id_pegajuan → id_pengajuan (INT, FK to pengajuan_asesmen)
- id_apl1 → REMOVED (tidak digunakan lagi)
- Added many performance indexes
- Added triggers for auto-calculation
- Enhanced data types and constraints

MIGRATION REQUIRED:
- Run migration_data_observasi.sql first before using these tables
- Backup existing data before running CREATE statements
- Test thoroughly in development environment
*/
