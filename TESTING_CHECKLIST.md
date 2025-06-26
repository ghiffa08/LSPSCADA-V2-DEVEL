# TESTING CHECKLIST - Database Normalization

## ✅ Pre-Migration Tests

### 1. Backup Current Data
```sql
-- Backup existing tables
CREATE TABLE asesor_backup AS SELECT * FROM asesor;
CREATE TABLE observasi_backup AS SELECT * FROM observasi;
CREATE TABLE pengajuan_asesmen_backup AS SELECT * FROM pengajuan_asesmen;
```

### 2. Verify Current Data Integrity
```sql
-- Check asesor data
SELECT COUNT(*) as total_asesor FROM asesor;
SELECT COUNT(*) as asesor_with_bidang FROM asesor WHERE bidang_kompetensi IS NOT NULL;

-- Check observasi data  
SELECT COUNT(*) as total_observasi FROM observasi;

-- Check relationships
SELECT COUNT(*) as total_pengajuan FROM pengajuan_asesmen;
```

## 🔄 Migration Tests

### 3. Run Migration
```bash
php spark migrate
```

### 4. Verify Migration Results
```sql
-- Check asesor_skema relationships created
SELECT COUNT(*) as total_relationships FROM asesor_skema;

-- Verify asesor data preserved
SELECT a.id_asesor, a.nomor_registrasi, GROUP_CONCAT(s.nama_skema) as skemas
FROM asesor a
LEFT JOIN asesor_skema ask ON ask.id_asesor = a.id_asesor  
LEFT JOIN skema s ON s.id_skema = ask.id_skema
GROUP BY a.id_asesor;

-- Check observasi still linked properly
SELECT COUNT(*) as observasi_with_valid_pengajuan 
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON pa.id_pengajuan = o.id_pengajuan;
```

## 🎯 Functional Tests

### 5. Model Tests
```php
// Test AsesorModel
$asesorModel = new \App\Models\AsesorModel();
$asesorWithSkema = $asesorModel->getAsesorWithSkema(4);
echo "Asesor competencies: " . $asesorWithSkema['bidang_kompetensi'];

// Test AsesorSkemaModel  
$asesorSkemaModel = new \App\Models\AsesorSkemaModel();
$skemas = $asesorSkemaModel->getSkemasByAsesor(4);
print_r($skemas);

// Test ObservasiModel
$observasiModel = new \App\Models\ObservasiModel();
$asesi = $observasiModel->getAsesiBySkema(1);
print_r($asesi);
```

### 6. Controller Tests
```bash
# Test observasi API endpoints
curl -X GET "http://localhost/api/observasi/skema-details?id_skema=1"
curl -X POST "http://localhost/api/observasi/save" -d '{"id_asesi":"ASI001","id_skema":1}'
```

### 7. Frontend Tests
- [ ] Asesor login and access observasi menu
- [ ] Select skema and verify asesi list loads
- [ ] Create new observasi record
- [ ] Save observasi details
- [ ] View existing observasi data

## 🔍 Data Integrity Verification

### 8. Cross-Reference Checks
```sql
-- Verify no orphaned records
SELECT 'asesor_skema orphaned asesor' as issue, COUNT(*) as count
FROM asesor_skema ask
LEFT JOIN asesor a ON a.id_asesor = ask.id_asesor  
WHERE a.id_asesor IS NULL;

SELECT 'asesor_skema orphaned skema' as issue, COUNT(*) as count
FROM asesor_skema ask
LEFT JOIN skema s ON s.id_skema = ask.id_skema
WHERE s.id_skema IS NULL;

-- Verify observasi relationships
SELECT 'observasi orphaned asesor' as issue, COUNT(*) as count
FROM observasi o
LEFT JOIN asesor a ON a.id_asesor = o.id_asesor
WHERE a.id_asesor IS NULL;

SELECT 'observasi orphaned pengajuan' as issue, COUNT(*) as count  
FROM observasi o
LEFT JOIN pengajuan_asesmen pa ON pa.id_pengajuan = o.id_pengajuan
WHERE pa.id_pengajuan IS NULL;
```

## ⚡ Performance Tests

### 9. Query Performance
```sql
-- Test join performance with EXPLAIN
EXPLAIN SELECT o.*, asesor_user.nama_lengkap AS nama_asesor, asesi_user.nama_lengkap AS nama_asesi, skema.nama_skema
FROM observasi o
INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
INNER JOIN users as asesi_user ON asesi_user.id = asesi.id_user
INNER JOIN pengajuan_asesmen ON pengajuan_asesmen.id_pengajuan = o.id_pengajuan
INNER JOIN asesor ON asesor.id_asesor = o.id_asesor
INNER JOIN users as asesor_user ON asesor_user.id = asesor.id_user
INNER JOIN skema ON skema.id_skema = pengajuan_asesmen.id_skema;
```

### 10. Index Verification
```sql
-- Check indexes exist
SHOW INDEX FROM asesor_skema;
SHOW INDEX FROM observasi;
SHOW INDEX FROM pengajuan_asesmen;
```

## 🚨 Rollback Plan

### 11. If Issues Found
```bash
# Rollback migration
php spark migrate:rollback

# Restore from backup if needed
DROP TABLE asesor;
CREATE TABLE asesor AS SELECT * FROM asesor_backup;
```

## ✅ Success Criteria

- [ ] All existing asesor data preserved
- [ ] Asesor-skema relationships created correctly  
- [ ] No orphaned records
- [ ] Observasi functionality works end-to-end
- [ ] Performance maintained or improved
- [ ] All tests pass

## 📝 Documentation Updates

- [ ] Update API documentation
- [ ] Update database schema documentation  
- [ ] Update developer setup guide
- [ ] Create migration notes for production
