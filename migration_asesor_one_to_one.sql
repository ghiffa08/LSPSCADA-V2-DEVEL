-- ===============================================================
-- MIGRASI DATA DARI MANY-TO-MANY KE ONE-TO-ONE
-- Script untuk migrasi data asesor_skema ke kolom id_skema di tabel asesor
-- ===============================================================

-- 1. Backup data yang ada (opsional tapi direkomendasikan)
CREATE TABLE asesor_backup_before_migration AS SELECT * FROM asesor;
CREATE TABLE asesor_skema_backup AS SELECT * FROM asesor_skema;

-- 2. Tambah kolom id_skema ke tabel asesor jika belum ada
ALTER TABLE asesor 
ADD COLUMN id_skema INT NULL AFTER nomor_registrasi,
ADD CONSTRAINT fk_asesor_skema FOREIGN KEY (id_skema) REFERENCES skema(id_skema) 
  ON UPDATE CASCADE ON DELETE SET NULL;

-- 3. Migrasi data: ambil skema pertama untuk setiap asesor
UPDATE asesor a
JOIN (
    SELECT id_asesor, MIN(id_skema) as id_skema
    FROM asesor_skema
    GROUP BY id_asesor
) x ON a.id_asesor = x.id_asesor
SET a.id_skema = x.id_skema;

-- 4. Verifikasi hasil migrasi
SELECT 
    a.id_asesor,
    a.nomor_registrasi,
    a.id_skema,
    s.nama_skema,
    u.nama_lengkap
FROM asesor a
LEFT JOIN skema s ON s.id_skema = a.id_skema
LEFT JOIN users u ON u.id = a.id_user
ORDER BY a.id_asesor;

-- 5. Cek asesor yang tidak memiliki skema setelah migrasi
SELECT 
    a.id_asesor,
    a.nomor_registrasi,
    u.nama_lengkap,
    'NO SKEMA ASSIGNED' as status
FROM asesor a
LEFT JOIN users u ON u.id = a.id_user
WHERE a.id_skema IS NULL;

-- 6. Setelah verifikasi berhasil, hapus tabel junction (HATI-HATI!)
-- DROP TABLE asesor_skema;

-- 7. Cleanup - hapus backup table jika migrasi berhasil (opsional)
-- DROP TABLE asesor_backup_before_migration;
-- DROP TABLE asesor_skema_backup;
