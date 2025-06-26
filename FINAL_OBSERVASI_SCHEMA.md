# 🗂 SQL SCHEMA FINAL - OBSERVASI SYSTEM

## 📋 **Struktur Tabel Terbaru dengan Relasi yang Benar**

Berikut adalah schema final untuk sistem observasi yang sudah direfactor dengan relasi yang benar menggunakan `id_pengajuan`:

### **1. Tabel `observasi` (Updated)**

```sql
CREATE TABLE `observasi` (
  `id_observasi` int NOT NULL AUTO_INCREMENT,
  `id_asesor` int NOT NULL COMMENT 'Foreign key to users table',
  `id_asesi` varchar(50) NOT NULL COMMENT 'Foreign key to asesi table',
  `id_pengajuan` int NOT NULL COMMENT 'Foreign key to pengajuan_asesmen table',
  `tanggal_observasi` date NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_observasi`),
  KEY `idx_observasi_pengajuan` (`id_pengajuan`),
  KEY `idx_observasi_asesi_tanggal` (`id_asesi`, `tanggal_observasi`),
  KEY `idx_observasi_asesor` (`id_asesor`),
  KEY `idx_observasi_composite` (`id_asesor`, `tanggal_observasi`, `id_pengajuan`),
  CONSTRAINT `observasi_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan_asesmen` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `observasi_id_asesor_foreign` FOREIGN KEY (`id_asesor`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `observasi_id_asesi_foreign` FOREIGN KEY (`id_asesi`) REFERENCES `asesi` (`id_asesi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabel utama untuk menyimpan data observasi asesmen';
```

### **2. Tabel `detail_observasi` (Optimized)**

```sql
CREATE TABLE `detail_observasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_observasi` int NOT NULL COMMENT 'Foreign key to observasi table',
  `id_skema` int NOT NULL COMMENT 'Foreign key to skema table',
  `id_kuk` int NOT NULL COMMENT 'Foreign key to kuk table',
  `kompeten` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Status kompetensi: Y=Kompeten, N=Belum Kompeten',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci COMMENT 'Catatan atau keterangan tambahan',
  `tanggal_observasi` date NOT NULL COMMENT 'Tanggal observasi (denormalized for performance)',
  PRIMARY KEY (`id`),
  KEY `idx_detail_observasi` (`id_observasi`),
  KEY `idx_detail_observasi_kuk` (`id_observasi`, `id_kuk`),
  KEY `idx_detail_observasi_skema` (`id_skema`, `kompeten`),
  KEY `idx_detail_tanggal` (`tanggal_observasi`),
  KEY `idx_detail_composite` (`id_observasi`, `id_skema`, `kompeten`),
  CONSTRAINT `detail_observasi_id_observasi_foreign` FOREIGN KEY (`id_observasi`) REFERENCES `observasi` (`id_observasi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_observasi_id_skema_foreign` FOREIGN KEY (`id_skema`) REFERENCES `skema` (`id_skema`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detail_observasi_id_kuk_foreign` FOREIGN KEY (`id_kuk`) REFERENCES `kuk` (`id_kuk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Tabel detail untuk menyimpan penilaian per KUK';
```

### **3. Tabel `pengajuan_asesmen` (Reference)**

```sql
CREATE TABLE `pengajuan_asesmen` (
  `id_pengajuan` int NOT NULL AUTO_INCREMENT,
  `id_asesi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `id_asesor` int DEFAULT NULL,
  `id_skema` int NOT NULL,
  `status_pengajuan` enum('pending','diterima','ditolak','selesai') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `tanggal_pengajuan` datetime DEFAULT NULL,
  `status_kompetensi` enum('kompeten','belum_kompeten') COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_pengajuan`),
  KEY `pengajuan_asesmen_id_asesor_foreign` (`id_asesor`),
  KEY `pengajuan_asesmen_id_skema_foreign` (`id_skema`),
  KEY `pengajuan_asesmen_id_asesi_foreign` (`id_asesi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## 🔗 **Relasi Antar Tabel**

```
pengajuan_asesmen (1) ──────── (N) observasi
       │                          │
       │ id_pengajuan              │ id_observasi
       │                          │
       └─────────────────────────── (N) detail_observasi
```

**Penjelasan Relasi:**
1. **`pengajuan_asesmen` → `observasi`**: Satu pengajuan bisa memiliki banyak observasi (multiple assessment sessions)
2. **`observasi` → `detail_observasi`**: Satu observasi memiliki banyak detail penilaian KUK
3. **`pengajuan_asesmen` → `detail_observasi`**: Melalui `observasi` (indirect relationship)

## 🚀 **Keunggulan Schema Baru**

### **1. Relasi yang Benar**
- ✅ Menggunakan `id_pengajuan` (integer) sebagai foreign key
- ✅ Relasi yang konsisten dengan tabel `pengajuan_asesmen`
- ✅ Foreign key constraints untuk data integrity

### **2. Performance Optimized**
- ✅ **Composite indexes** untuk query yang sering digunakan
- ✅ **Selective indexes** berdasarkan pola query aplikasi
- ✅ **Denormalized `tanggal_observasi`** di detail untuk performa

### **3. Data Integrity**
- ✅ **CASCADE DELETE/UPDATE** untuk konsistensi data
- ✅ **ENUM constraints** untuk status yang valid
- ✅ **NOT NULL constraints** untuk field wajib

### **4. Scalability**
- ✅ **Proper indexing** untuk query cepat pada data besar
- ✅ **Normalized structure** dengan optimasi performa
- ✅ **Efficient joins** dengan foreign key yang tepat

## 📊 **Sample Data Structure**

```sql
-- Sample pengajuan_asesmen
INSERT INTO pengajuan_asesmen VALUES 
(1, 'AS001', 123, 1, 'diterima', '2025-06-01 10:00:00', 'kompeten');

-- Sample observasi  
INSERT INTO observasi VALUES 
(1, 123, 'AS001', 1, '2025-06-23', NOW(), NOW());

-- Sample detail_observasi
INSERT INTO detail_observasi VALUES 
(1, 1, 1, 101, 'Y', 'Sangat baik dalam teknik dasar', '2025-06-23'),
(2, 1, 1, 102, 'N', 'Perlu perbaikan pada prosedur keselamatan', '2025-06-23');
```

## 🔧 **Query Examples**

### **Get Observasi with Complete Data**
```sql
SELECT 
    o.id_observasi,
    o.tanggal_observasi,
    pa.id_pengajuan,
    asesor.nama_lengkap as nama_asesor,
    asesi_user.nama_lengkap as nama_asesi,
    skema.nama_skema,
    COUNT(do.id) as total_kuk,
    SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) as kompeten_count,
    ROUND((SUM(CASE WHEN do.kompeten = 'Y' THEN 1 ELSE 0 END) / COUNT(do.id)) * 100, 2) as persentase_kompeten
FROM observasi o
INNER JOIN pengajuan_asesmen pa ON o.id_pengajuan = pa.id_pengajuan
INNER JOIN asesi ON asesi.id_asesi = o.id_asesi
INNER JOIN users asesi_user ON asesi_user.id = asesi.id_user
INNER JOIN users asesor ON asesor.id = o.id_asesor
INNER JOIN skema ON skema.id_skema = pa.id_skema
LEFT JOIN detail_observasi do ON do.id_observasi = o.id_observasi
GROUP BY o.id_observasi;
```

### **Get KUK Details for Observasi**
```sql
SELECT 
    do.*,
    kuk.kode_kuk,
    kuk.nama_kuk,
    unit.nama_unit,
    elemen.nama_elemen
FROM detail_observasi do
INNER JOIN kuk ON kuk.id_kuk = do.id_kuk
INNER JOIN elemen ON elemen.id_elemen = kuk.id_elemen
INNER JOIN unit ON unit.id_unit = elemen.id_unit
WHERE do.id_observasi = ?
ORDER BY unit.kode_unit, elemen.kode_elemen, kuk.kode_kuk;
```

## 🛠 **Migration Command**

Untuk mengupdate schema dari `id_pegajuan` ke `id_pengajuan`, jalankan:

```bash
# 1. Backup data
mysqldump -u username -p database_name observasi detail_observasi > backup_observasi_$(date +%Y%m%d).sql

# 2. Run migration
mysql -u username -p database_name < simple_migration_observasi.sql

# 3. Verify results
mysql -u username -p database_name -e "DESCRIBE observasi; SELECT COUNT(*) FROM observasi;"
```

---

**🎉 Schema sudah production-ready dengan relasi yang benar, performa optimal, dan data integrity yang terjamin!**
