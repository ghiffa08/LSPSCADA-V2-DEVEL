# CHECKLIST TESTING - Migrasi Asesor dari Many-to-Many ke One-to-One

## ✅ Pre-Migration Tests

### 1. Backup Data
```sql
-- Backup tabel yang akan diubah
CREATE TABLE asesor_backup AS SELECT * FROM asesor;
CREATE TABLE asesor_skema_backup AS SELECT * FROM asesor_skema;
```

### 2. Verifikasi Data Existing
```sql
-- Cek jumlah relasi di tabel junction
SELECT COUNT(*) as total_relations FROM asesor_skema;

-- Cek distribusi skema per asesor
SELECT id_asesor, COUNT(id_skema) as skema_count 
FROM asesor_skema 
GROUP BY id_asesor 
ORDER BY skema_count DESC;
```

## ✅ Migration Tests

### 1. Jalankan Migration
```bash
php spark migrate
```

### 2. Verifikasi Schema
```sql
-- Cek struktur tabel asesor baru
DESCRIBE asesor;

-- Cek foreign key constraint
SHOW CREATE TABLE asesor;
```

### 3. Verifikasi Data Migration
```sql
-- Cek asesor yang berhasil dimigrasikan
SELECT a.id_asesor, a.id_skema, s.nama_skema, u.nama_lengkap
FROM asesor a
LEFT JOIN skema s ON s.id_skema = a.id_skema
LEFT JOIN users u ON u.id = a.id_user
WHERE a.id_skema IS NOT NULL;

-- Cek asesor tanpa skema
SELECT a.id_asesor, u.nama_lengkap
FROM asesor a
LEFT JOIN users u ON u.id = a.id_user
WHERE a.id_skema IS NULL;
```

## ✅ Application Tests

### 1. Test Model Methods
```php
// Test di tinker atau buat test script
$asesorModel = new \App\Models\AsesorModel();

// Test getWithSkema
$asesor = $asesorModel->getWithSkema(1);
var_dump($asesor);

// Test updateAsesorSkema
$result = $asesorModel->updateAsesorSkema(1, 2);
var_dump($result);
```

### 2. Test Form Submission

#### a. Test Tambah Asesor Baru
- [ ] Buka halaman admin kelola users
- [ ] Klik "Tambah Asesor" 
- [ ] Isi form dengan data valid
- [ ] Pilih SATU skema dari dropdown (bukan multiple)
- [ ] Submit form
- [ ] Verifikasi: Data asesor tersimpan dengan skema yang benar

#### b. Test Edit Asesor
- [ ] Edit asesor existing
- [ ] Ubah skema yang dipilih
- [ ] Submit form
- [ ] Verifikasi: Skema berubah di database

### 3. Test API Endpoints

#### a. Test Create Asesor API
```bash
curl -X POST "http://localhost/yourapp/api/user-management/create-asesor-user" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "test_asesor",
    "email": "test@example.com", 
    "nama_lengkap": "Test Asesor",
    "password": "password123",
    "skema_id": 1
  }'
```

#### b. Test Get Active Skemas
```bash
curl -X GET "http://localhost/yourapp/api/user-management/get-active-skemas"
```

### 4. Test Observasi Controller
- [ ] Login sebagai asesor
- [ ] Akses halaman observasi
- [ ] Verifikasi: Hanya muncul skema yang assigned ke asesor
- [ ] Verifikasi: Tidak ada error terkait multiple skema

### 5. Test UI/UX
- [ ] Form asesor menggunakan single select (bukan multiple)
- [ ] Dropdown skema ter-load dengan benar
- [ ] Select2 berfungsi normal
- [ ] Validation error muncul jika skema tidak dipilih

## ✅ Database Consistency Tests

### 1. Referential Integrity
```sql
-- Test foreign key constraint
INSERT INTO asesor (id_user, nomor_registrasi, id_skema) 
VALUES (999, 'TEST-001', 99999); -- Should fail if skema doesn't exist

-- Test cascade update
UPDATE skema SET id_skema = 999 WHERE id_skema = 1;
-- Check if asesor.id_skema updated accordingly

-- Test set null on delete
DELETE FROM skema WHERE id_skema = 1;
-- Check if asesor.id_skema set to NULL
```

### 2. Data Validation
```sql
-- Cek duplikasi user
SELECT id_user, COUNT(*) 
FROM asesor 
GROUP BY id_user 
HAVING COUNT(*) > 1;

-- Cek data orphan
SELECT a.* FROM asesor a
LEFT JOIN users u ON u.id = a.id_user
WHERE u.id IS NULL;
```

## ✅ Performance Tests

### 1. Query Performance
```sql
-- Test performa query join
EXPLAIN SELECT a.*, s.nama_skema, u.nama_lengkap
FROM asesor a
LEFT JOIN skema s ON s.id_skema = a.id_skema
LEFT JOIN users u ON u.id = a.id_user;
```

### 2. Index Verification
```sql
-- Cek index pada kolom id_skema
SHOW INDEX FROM asesor WHERE Column_name = 'id_skema';
```

## ✅ Rollback Tests

### 1. Test Rollback Migration
```bash
php spark migrate:rollback
```

### 2. Verify Rollback
```sql
-- Cek apakah tabel junction ter-restore
SELECT COUNT(*) FROM asesor_skema;

-- Cek apakah kolom id_skema terhapus
DESCRIBE asesor;
```

## ✅ Final Verification

### 1. End-to-End Test
- [ ] Buat asesor baru dengan skema tertentu
- [ ] Login sebagai asesor tersebut
- [ ] Akses fitur observasi
- [ ] Buat observasi baru
- [ ] Verifikasi data tersimpan dengan benar

### 2. Clean Up Testing Data
```sql
-- Hapus data testing jika diperlukan
DELETE FROM asesor WHERE nomor_registrasi LIKE 'TEST-%';
DELETE FROM users WHERE username LIKE 'test_%';
```

## 📋 Expected Results

Setelah semua test passed:
- ✅ Setiap asesor hanya memiliki maksimal 1 skema
- ✅ Form menggunakan single select
- ✅ API menerima `skema_id` bukan `skema_ids[]`
- ✅ Database konsisten dan tidak ada data orphan
- ✅ Fitur observasi berjalan normal
- ✅ Performance tidak menurun

## 🚨 Red Flags

Hentikan testing dan investigasi jika:
- ❌ Ada asesor yang kehilangan data skema setelah migration
- ❌ Foreign key constraint error
- ❌ Form submission gagal
- ❌ Observasi controller error
- ❌ Performance menurun drastis
