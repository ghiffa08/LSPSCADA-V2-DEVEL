-- SQL script to check and fix asesor-skema relationships

-- Check the asesor_skema table structure
DESCRIBE asesor_skema;

-- Count entries in the asesor_skema table
SELECT COUNT(*) as total_relationships FROM asesor_skema;

-- Check asesors without any skema assignments
SELECT a.id_asesor, a.nomor_registrasi, u.username, u.nama_lengkap, u.email,
       COUNT(ask.id_skema) as skema_count
FROM asesor a
LEFT JOIN asesor_skema ask ON a.id_asesor = ask.id_asesor
LEFT JOIN users u ON a.id_user = u.id
GROUP BY a.id_asesor
HAVING skema_count = 0;

-- Check how many skemas each asesor has
SELECT a.id_asesor, a.nomor_registrasi, u.username, u.nama_lengkap, 
       COUNT(ask.id_skema) as skema_count
FROM asesor a
LEFT JOIN asesor_skema ask ON a.id_asesor = ask.id_asesor
LEFT JOIN users u ON a.id_user = u.id
GROUP BY a.id_asesor
ORDER BY skema_count ASC;

-- List all asesor-skema relationships with details
SELECT a.id_asesor, a.nomor_registrasi, u.username, u.nama_lengkap,
       s.id_skema, s.kode_skema, s.nama_skema
FROM asesor a
JOIN asesor_skema ask ON a.id_asesor = ask.id_asesor
JOIN skema s ON ask.id_skema = s.id_skema
JOIN users u ON a.id_user = u.id
ORDER BY a.id_asesor, s.id_skema;

-- Check skema table
SELECT * FROM skema WHERE status = 'Y' ORDER BY nama_skema;
