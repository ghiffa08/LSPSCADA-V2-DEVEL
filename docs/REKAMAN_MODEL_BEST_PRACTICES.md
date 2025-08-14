# RekamanAsesmenModel & KompetensiModel - Best Practices & Performance Guide

## Method Documentation & Usage

### RekamanAsesmenModel
- `getRekamanWithDetails($id_rekaman)`
  - Ambil data rekaman + relasi + kompetensi (cached 15m)
- `getProgressStats($id_rekaman)`
  - Statistik progress (cached 5m)
- `batchUpdateKompetensi($id_rekaman, $kompetensiData)`
  - Batch upsert kompetensi, auto invalidate cache
- `getRekamanList($filters, $page, $perPage)`
  - List rekaman dengan filter & pagination
- `getExistingKompetensiData($id_rekaman)`
  - Data kompetensi untuk form
- `updateProgressStats($id_rekaman)`
  - Update progress & status rekaman

### RekamanAsesmenKompetensiModel
- `batchUpsertKompetensi($id_rekaman, $kompetensiData)`
  - Batch upsert kompetensi (robust, transactional)
- `getKompetensiStats($id_rekaman)`
  - Statistik kompetensi untuk progress

## Performance Benchmarks
- Query single record: <200ms
- List 50 records: <500ms
- Batch 100 kompetensi: <1s
- Memory usage: <50MB

## Caching Strategy
- TTL 15m (rekaman details), 5m (progress)
- Invalidate cache on update/batch

## Best Practices
- Gunakan batch operation untuk update massal
- Selalu gunakan transaction untuk batch
- Validasi cross-table (cek foreign key)
- Logging error pada catch/rollback
- Gunakan eager loading untuk relasi

## Maintenance
- Jalankan migration index untuk performa optimal
- Monitor query time dan memory usage
- Update TTL cache sesuai kebutuhan traffic

---

**Last updated:** 2025-07-02
