<?php

namespace App\Services;

use App\Models\ObservasiModel;
use App\Models\DetailObservasiModel;
use App\Models\SkemaModel;
use App\Requests\ObservasiRequest;
use CodeIgniter\Database\BaseConnection;
use Config\Database;
use Config\Cache;

/**
 * ObservasiService - Optimized
 * 
 * Enhanced service class untuk business logic observasi
 * dengan caching, validation, dan optimasi query
 */
class ObservasiService
{
    protected ObservasiModel $observasiModel;
    protected DetailObservasiModel $detailModel;
    protected SkemaModel $skemaModel;
    protected BaseConnection $db;
    protected ObservasiRequest $request;
    protected $cache;

    public function __construct()
    {
        $this->observasiModel = new ObservasiModel();
        $this->detailModel = new DetailObservasiModel();
        $this->skemaModel = new SkemaModel();
        $this->db = Database::connect();
        $this->request = new ObservasiRequest();
        $this->cache = Cache::handler();
    }

    /**
     * Create or update observation with batch details - OPTIMIZED
     * Enhanced with caching, better validation, and transaction safety
     */
    public function saveObservation(array $data): array
    {
        // Validate and sanitize input
        $validatedData = $this->validateAndSanitizeInput($data);
        if (isset($validatedData['error'])) {
            return [
                'success' => false,
                'message' => $validatedData['error'],
                'data' => null
            ];
        }

        $this->db->transStart();

        try {
            // Prepare main observation data
            $observationData = [
                'id_asesor' => $validatedData['id_asesor'],
                'id_asesi' => $validatedData['id_asesi'],
                'id_pengajuan' => $validatedData['id_pengajuan'],
                'tanggal_observasi' => $validatedData['tanggal_observasi'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Check if observation already exists
            $existingObservation = $this->observasiModel
                ->where('id_asesi', $validatedData['id_asesi'])
                ->where('id_pengajuan', $validatedData['id_pengajuan'])
                ->where('tanggal_observasi', $validatedData['tanggal_observasi'])
                ->first();

            if ($existingObservation) {
                // Update existing observation
                $id_observasi = $existingObservation['id_observasi'];
                $this->observasiModel->update($id_observasi, $observationData);
            } else {
                // Create new observation
                $observationData['created_at'] = date('Y-m-d H:i:s');
                $observationData['status'] = 'draft';
                $id_observasi = $this->observasiModel->insert($observationData);
            }

            // Enhanced batch upsert for details
            if (isset($validatedData['details']) && !empty($validatedData['details'])) {
                $this->optimizedUpsertDetails($id_observasi, $validatedData['details']);
            }

            // Update progress and status
            $this->updateObservasiProgress($id_observasi);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Clear related caches
            $this->clearObservasiCaches($validatedData['id_asesi'], $validatedData['id_asesor']);

            // Return success response with summary
            $summary = $this->getObservationSummary($id_observasi);

            return [
                'success' => true,
                'message' => 'Observasi berhasil disimpan',
                'data' => [
                    'id_observasi' => $id_observasi,
                    'summary' => $summary
                ]
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'ObservasiService saveObservation Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menyimpan observasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get observation with details using eager loading - OPTIMIZED
     * Enhanced dengan caching dan optimasi query
     */
    public function getObservationWithDetails(int $id_observasi): array
    {
        $cacheKey = "observasi_details_{$id_observasi}";

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Optimized query dengan single JOIN
            $observation = $this->db->table('observasi o')
                ->select([
                    'o.*',
                    'asesi_user.nama_lengkap as nama_asesi',
                    'asesor_user.nama_lengkap as nama_asesor',
                    'skema.nama_skema',
                    'skema.kode_skema',
                    'skema.id_skema'
                ])
                ->join('asesi', 'asesi.id_asesi = o.id_asesi', 'inner')
                ->join('users as asesi_user', 'asesi_user.id = asesi.id_user', 'inner')
                ->join('asesor', 'asesor.id_asesor = o.id_asesor', 'inner')
                ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
                ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = o.id_pengajuan', 'inner')
                ->join('skema', 'skema.id_skema = pa.id_skema', 'inner')
                ->where('o.id_observasi', $id_observasi)
                ->get()
                ->getRowArray();

            if (!$observation) {
                return [
                    'success' => false,
                    'message' => 'Observasi tidak ditemukan',
                    'data' => null
                ];
            }

            // Get details dengan optimized query
            $details = $this->getOptimizedDetails($id_observasi, $observation['id_skema']);

            // Get summary from cache or calculate
            $summary = $this->getObservationSummary($id_observasi);

            // Group details by unit untuk UX yang lebih baik
            $groupedDetails = $this->groupDetailsByUnit($details);

            $result = [
                'success' => true,
                'message' => 'Data observasi berhasil diambil',
                'data' => [
                    'observation' => $observation,
                    'details' => $groupedDetails,
                    'summary' => $summary
                ]
            ];

            // Cache for 10 minutes
            $this->cache->save($cacheKey, $result, 600);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'ObservasiService getObservationWithDetails Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data observasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get optimized details untuk observasi
     */
    private function getOptimizedDetails(int $idObservasi, int $idSkema): array
    {
        return $this->db->table('detail_observasi do')
            ->select([
                'do.id',
                'do.id_kuk',
                'do.kompeten',
                'do.keterangan',
                'k.kode_kuk',
                'k.nama_kuk',
                'e.kode_elemen',
                'e.nama_elemen',
                'u.kode_unit',
                'u.nama_unit'
            ])
            ->join('kuk k', 'k.id_kuk = do.id_kuk', 'inner')
            ->join('elemen e', 'e.id_elemen = k.id_elemen', 'inner')
            ->join('unit u', 'u.id_unit = e.id_unit', 'inner')
            ->where('do.id_observasi', $idObservasi)
            ->orderBy('u.kode_unit')
            ->orderBy('e.kode_elemen')
            ->orderBy('k.kode_kuk')
            ->get()
            ->getResultArray();
    }

    /**
     * Get KUK structure for observation form - OPTIMIZED
     * Enhanced dengan caching dan optimasi query
     */
    public function getKukStructureForSchema(int $id_skema, ?string $id_asesi = null): array
    {
        $cacheKey = "kuk_structure_{$id_skema}" . ($id_asesi ? "_{$id_asesi}" : '');

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            // Optimized query dengan subquery untuk existing data
            $builder = $this->db->table('skema s');

            if ($id_asesi) {
                // Include existing observasi data
                $builder->select([
                    'u.id_unit',
                    'u.kode_unit',
                    'u.nama_unit',
                    'e.id_elemen',
                    'e.kode_elemen',
                    'e.nama_elemen',
                    'k.id_kuk',
                    'k.kode_kuk',
                    'k.nama_kuk',
                    'COALESCE(do.kompeten, "") as existing_kompeten',
                    'COALESCE(do.keterangan, "") as existing_keterangan'
                ]);

                $builder->join('kelompok_kerja kk', 'kk.id_skema = s.id_skema', 'inner')
                    ->join('kelompok_unit ku', 'ku.id_kelompok = kk.id_kelompok', 'inner')
                    ->join('unit u', 'u.id_unit = ku.id_unit AND u.id_skema = s.id_skema', 'inner')
                    ->join('elemen e', 'e.id_unit = u.id_unit AND e.id_skema = s.id_skema', 'inner')
                    ->join('kuk k', 'k.id_elemen = e.id_elemen AND k.id_unit = u.id_unit AND k.id_skema = s.id_skema', 'inner')
                    ->join('observasi o', 'o.id_asesi = "' . $this->db->escapeString($id_asesi) . '"', 'left')
                    ->join('detail_observasi do', 'do.id_observasi = o.id_observasi AND do.id_kuk = k.id_kuk', 'left');
            } else {
                // Basic structure without existing data
                $builder->select([
                    'u.id_unit',
                    'u.kode_unit',
                    'u.nama_unit',
                    'e.id_elemen',
                    'e.kode_elemen',
                    'e.nama_elemen',
                    'k.id_kuk',
                    'k.kode_kuk',
                    'k.nama_kuk'
                ]);

                $builder->join('kelompok_kerja kk', 'kk.id_skema = s.id_skema', 'inner')
                    ->join('kelompok_unit ku', 'ku.id_kelompok = kk.id_kelompok', 'inner')
                    ->join('unit u', 'u.id_unit = ku.id_unit AND u.id_skema = s.id_skema', 'inner')
                    ->join('elemen e', 'e.id_unit = u.id_unit AND e.id_skema = s.id_skema', 'inner')
                    ->join('kuk k', 'k.id_elemen = e.id_elemen AND k.id_unit = u.id_unit AND k.id_skema = s.id_skema', 'inner');
            }

            $structure = $builder
                ->where('s.id_skema', $id_skema)
                ->where('s.status', 'Y')
                ->where('u.status', 'Y')
                ->orderBy('u.kode_unit')
                ->orderBy('e.kode_elemen')
                ->orderBy('k.kode_kuk')
                ->get()
                ->getResultArray();

            $groupedStructure = $this->groupKukStructure($structure, $id_asesi !== null);

            $result = [
                'success' => true,
                'message' => 'Struktur KUK berhasil diambil',
                'data' => $groupedStructure
            ];

            // Cache for 30 minutes
            $this->cache->save($cacheKey, $result, 1800);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'ObservasiService getKukStructureForSchema Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil struktur KUK: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Group details by unit for better presentation
     */
    private function groupDetailsByUnit(array $details): array
    {
        $grouped = [];

        foreach ($details as $detail) {
            $unitKey = $detail['kode_unit'];

            if (!isset($grouped[$unitKey])) {
                $grouped[$unitKey] = [
                    'unit_info' => [
                        'kode_unit' => $detail['kode_unit'],
                        'nama_unit' => $detail['nama_unit']
                    ],
                    'elements' => []
                ];
            }

            $elemenKey = $detail['kode_elemen'];
            if (!isset($grouped[$unitKey]['elements'][$elemenKey])) {
                $grouped[$unitKey]['elements'][$elemenKey] = [
                    'element_info' => [
                        'kode_elemen' => $detail['kode_elemen'],
                        'nama_elemen' => $detail['nama_elemen']
                    ],
                    'kuks' => []
                ];
            }

            $grouped[$unitKey]['elements'][$elemenKey]['kuks'][] = [
                'id' => $detail['id'],
                'id_kuk' => $detail['id_kuk'],
                'kode_kuk' => $detail['kode_kuk'],
                'nama_kuk' => $detail['nama_kuk'],
                'kompeten' => $detail['kompeten'],
                'keterangan' => $detail['keterangan']
            ];
        }

        return $grouped;
    }

    /**
     * Group KUK structure for form display - ENHANCED
     */
    private function groupKukStructure(array $structure, bool $includeExisting = false): array
    {
        $grouped = [];
        $totalKUK = 0;
        $existingData = [];

        foreach ($structure as $item) {
            $unitKey = $item['kode_unit'];
            $totalKUK++;

            if (!isset($grouped[$unitKey])) {
                $grouped[$unitKey] = [
                    'unit_info' => [
                        'id_unit' => $item['id_unit'],
                        'kode_unit' => $item['kode_unit'],
                        'nama_unit' => $item['nama_unit']
                    ],
                    'elements' => []
                ];
            }

            $elemenKey = $item['kode_elemen'];
            if (!isset($grouped[$unitKey]['elements'][$elemenKey])) {
                $grouped[$unitKey]['elements'][$elemenKey] = [
                    'element_info' => [
                        'id_elemen' => $item['id_elemen'],
                        'kode_elemen' => $item['kode_elemen'],
                        'nama_elemen' => $item['nama_elemen']
                    ],
                    'kuks' => []
                ];
            }

            $kukData = [
                'id_kuk' => $item['id_kuk'],
                'kode_kuk' => $item['kode_kuk'],
                'nama_kuk' => $item['nama_kuk']
            ];

            // Add existing data jika ada
            if ($includeExisting) {
                $kukData['existing_kompeten'] = $item['existing_kompeten'] ?? '';
                $kukData['existing_keterangan'] = $item['existing_keterangan'] ?? '';

                // Store existing data untuk keperluan lain
                if (!empty($item['existing_kompeten'])) {
                    $existingData[$item['id_kuk']] = [
                        'kompeten' => $item['existing_kompeten'],
                        'keterangan' => $item['existing_keterangan']
                    ];
                }
            }

            $grouped[$unitKey]['elements'][$elemenKey]['kuks'][] = $kukData;
        }

        return [
            'structure' => $grouped,
            'totalKUK' => $totalKUK,
            'existingData' => $existingData
        ];
    }

    /**
     * Get observation list with pagination and filtering
     */
    public function getObservationList(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        try {
            $builder = $this->observasiModel
                ->select([
                    'observasi.*',
                    'asesi_user.nama_lengkap as nama_asesi',
                    'asesor_user.nama_lengkap as nama_asesor',
                    'skema.nama_skema'
                ])->join('asesi', 'asesi.id_asesi = observasi.id_asesi', 'inner')
                ->join('users as asesi_user', 'asesi_user.id = asesi.id_user', 'inner')
                ->join('users as asesor_user', 'asesor_user.id = observasi.id_asesor', 'inner')
                ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_pengajuan = observasi.id_pengajuan', 'inner')
                ->join('skema', 'skema.id_skema = pengajuan_asesmen.id_skema', 'inner');

            // Apply filters
            if (!empty($filters['search'])) {
                $builder->groupStart()
                    ->like('asesi_user.nama_lengkap', $filters['search'])
                    ->orLike('asesor_user.nama_lengkap', $filters['search'])
                    ->orLike('skema.nama_skema', $filters['search'])
                    ->groupEnd();
            }

            if (!empty($filters['tanggal_dari'])) {
                $builder->where('observasi.tanggal_observasi >=', $filters['tanggal_dari']);
            }

            if (!empty($filters['tanggal_sampai'])) {
                $builder->where('observasi.tanggal_observasi <=', $filters['tanggal_sampai']);
            }

            if (!empty($filters['id_asesor'])) {
                $builder->where('observasi.id_asesor', $filters['id_asesor']);
            }

            // Get total records for pagination
            $total = $builder->countAllResults(false);

            // Get paginated results
            $observations = $builder
                ->orderBy('observasi.tanggal_observasi', 'DESC')
                ->paginate($perPage, 'default', $page);

            return [
                'success' => true,
                'message' => 'Data observasi berhasil diambil',
                'data' => [
                    'observations' => $observations,
                    'pagination' => [
                        'total' => $total,
                        'page' => $page,
                        'per_page' => $perPage,
                        'total_pages' => ceil($total / $perPage)
                    ]
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'ObservasiService Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data observasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Enhanced input validation and sanitization
     */
    private function validateAndSanitizeInput(array $data): array
    {
        try {
            // Sanitize scalar inputs
            $sanitized = [];

            // Required fields validation
            $requiredFields = ['id_asesor', 'id_asesi', 'id_pengajuan', 'tanggal_observasi'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    return ['error' => "Field {$field} wajib diisi"];
                }
            }

            // Sanitize and validate fields
            $sanitized['id_asesor'] = filter_var($data['id_asesor'], FILTER_VALIDATE_INT);
            if (!$sanitized['id_asesor']) {
                return ['error' => 'ID Asesor tidak valid'];
            }

            $sanitized['id_asesi'] = trim(strip_tags($data['id_asesi']));
            if (empty($sanitized['id_asesi'])) {
                return ['error' => 'ID Asesi tidak valid'];
            }

            $sanitized['id_pengajuan'] = filter_var($data['id_pengajuan'], FILTER_VALIDATE_INT);
            if (!$sanitized['id_pengajuan']) {
                return ['error' => 'ID Pengajuan tidak valid'];
            }

            // Validate date
            $date = date_parse($data['tanggal_observasi']);
            if ($date['error_count'] > 0 || !checkdate($date['month'], $date['day'], $date['year'])) {
                return ['error' => 'Tanggal observasi tidak valid'];
            }
            $sanitized['tanggal_observasi'] = date('Y-m-d', strtotime($data['tanggal_observasi']));

            // Validate and sanitize details if present
            if (isset($data['details']) && is_array($data['details'])) {
                $sanitized['details'] = [];
                foreach ($data['details'] as $detail) {
                    if (!isset($detail['id_kuk']) || !isset($detail['kompeten'])) {
                        continue;
                    }

                    $sanitizedDetail = [
                        'id_kuk' => filter_var($detail['id_kuk'], FILTER_VALIDATE_INT),
                        'kompeten' => in_array($detail['kompeten'], ['Y', 'N']) ? $detail['kompeten'] : '',
                        'keterangan' => isset($detail['keterangan']) ?
                            substr(trim(strip_tags($detail['keterangan'])), 0, 1000) : ''
                    ];

                    if ($sanitizedDetail['id_kuk'] && $sanitizedDetail['kompeten']) {
                        $sanitized['details'][] = $sanitizedDetail;
                    }
                }
            }

            return $sanitized;
        } catch (\Exception $e) {
            log_message('error', 'Validation error: ' . $e->getMessage());
            return ['error' => 'Terjadi kesalahan validasi data'];
        }
    }

    /**
     * Optimized batch upsert for detail observasi
     */
    private function optimizedUpsertDetails(int $idObservasi, array $details): void
    {
        if (empty($details)) {
            return;
        }

        // Get existing details for comparison
        $existing = $this->db->table('detail_observasi')
            ->where('id_observasi', $idObservasi)
            ->get()
            ->getResultArray();

        $existingMap = [];
        foreach ($existing as $detail) {
            $existingMap[$detail['id_kuk']] = $detail;
        }

        $toInsert = [];
        $toUpdate = [];

        foreach ($details as $detail) {
            $detailData = [
                'kompeten' => $detail['kompeten'],
                'keterangan' => $detail['keterangan'],
                'tanggal_observasi' => $detail['tanggal_observasi'] ?? date('Y-m-d')
            ];

            if (isset($existingMap[$detail['id_kuk']])) {
                // Update existing
                $toUpdate[] = array_merge($detailData, [
                    'id' => $existingMap[$detail['id_kuk']]['id']
                ]);
            } else {
                // Insert new
                $toInsert[] = array_merge($detailData, [
                    'id_observasi' => $idObservasi,
                    'id_kuk' => $detail['id_kuk']
                ]);
            }
        }

        // Execute batch operations
        if (!empty($toUpdate)) {
            $this->db->table('detail_observasi')->updateBatch($toUpdate, 'id');
        }

        if (!empty($toInsert)) {
            $this->db->table('detail_observasi')->insertBatch($toInsert);
        }
    }

    /**
     * Update observasi progress and status
     */
    private function updateObservasiProgress(int $idObservasi): void
    {
        try {
            $stats = $this->db->table('detail_observasi')
                ->select('
                    COUNT(*) as total_kuk,
                    COUNT(CASE WHEN kompeten = "Y" THEN 1 END) as kompeten_count
                ')
                ->where('id_observasi', $idObservasi)
                ->get()
                ->getRowArray();

            $progress = 0;
            if ($stats['total_kuk'] > 0) {
                $progress = ($stats['kompeten_count'] / $stats['total_kuk']) * 100;
            }

            // Determine status
            $status = 'draft';
            if ($progress > 0 && $progress < 100) {
                $status = 'in_progress';
            } elseif ($progress >= 100) {
                $status = 'completed';
            }

            $this->db->table('observasi')
                ->where('id_observasi', $idObservasi)
                ->update([
                    'total_kuk' => $stats['total_kuk'],
                    'kompeten_count' => $stats['kompeten_count'],
                    'progress_percentage' => round($progress, 2),
                    'status' => $status,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } catch (\Exception $e) {
            log_message('error', 'Error updating observasi progress: ' . $e->getMessage());
        }
    }

    /**
     * Get observation summary with caching
     */
    public function getObservationSummary(int $idObservasi): array
    {
        $cacheKey = "observasi_summary_{$idObservasi}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $summary = $this->db->table('observasi o')
                ->select('
                    o.total_kuk,
                    o.kompeten_count,
                    o.progress_percentage,
                    o.status,
                    COUNT(CASE WHEN do.kompeten = "N" THEN 1 END) as tidak_kompeten_count
                ')
                ->join('detail_observasi do', 'do.id_observasi = o.id_observasi', 'left')
                ->where('o.id_observasi', $idObservasi)
                ->groupBy('o.id_observasi')
                ->get()
                ->getRowArray();

            if (!$summary) {
                $summary = [
                    'total_kuk' => 0,
                    'kompeten_count' => 0,
                    'tidak_kompeten_count' => 0,
                    'progress_percentage' => 0,
                    'status' => 'draft'
                ];
            }

            // Cache for 5 minutes
            $this->cache->save($cacheKey, $summary, 300);

            return $summary;
        } catch (\Exception $e) {
            log_message('error', 'Error getting observation summary: ' . $e->getMessage());
            return [
                'total_kuk' => 0,
                'kompeten_count' => 0,
                'tidak_kompeten_count' => 0,
                'progress_percentage' => 0,
                'status' => 'draft'
            ];
        }
    }

    /**
     * Clear observasi related caches
     */
    private function clearObservasiCaches(string $idAsesi, int $idAsesor): void
    {
        try {
            // Clear specific caches
            $this->cache->deleteMatching("observasi_structure_*_{$idAsesi}");
            $this->cache->deleteMatching("observasi_summary_*");
            $this->cache->delete("asesor_stats_{$idAsesor}");
            $this->cache->deleteMatching("asesi_by_skema_*");
        } catch (\Exception $e) {
            log_message('error', 'Error clearing caches: ' . $e->getMessage());
        }
    }

    /**
     * Batch save untuk multiple KUK - OPTIMIZED
     */
    public function batchSaveKUK(int $idObservasi, array $items): array
    {
        $this->db->transStart();

        try {
            // Validate items
            $validatedItems = [];
            foreach ($items as $idKuk => $item) {
                if (!filter_var($idKuk, FILTER_VALIDATE_INT)) {
                    continue;
                }

                if (!isset($item['kompeten']) || !in_array($item['kompeten'], ['Y', 'N'])) {
                    continue;
                }

                $validatedItems[$idKuk] = [
                    'kompeten' => $item['kompeten'],
                    'keterangan' => isset($item['keterangan']) ?
                        substr(trim(strip_tags($item['keterangan'])), 0, 1000) : ''
                ];
            }

            if (empty($validatedItems)) {
                throw new \Exception('Tidak ada data valid untuk disimpan');
            }

            // Execute optimized upsert
            $this->optimizedBatchUpsert($idObservasi, $validatedItems);

            // Update progress
            $this->updateObservasiProgress($idObservasi);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Clear caches
            $observasi = $this->db->table('observasi')->where('id_observasi', $idObservasi)->get()->getRowArray();
            if ($observasi) {
                $this->clearObservasiCaches($observasi['id_asesi'], $observasi['id_asesor']);
            }

            return [
                'success' => true,
                'processed' => count($validatedItems),
                'message' => 'Batch save berhasil'
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error in batchSaveKUK: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal batch save: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Optimized batch upsert untuk detail observasi
     */
    private function optimizedBatchUpsert(int $idObservasi, array $items): void
    {
        $detailTable = 'detail_observasi';

        // Get existing records
        $existing = $this->db->table($detailTable)
            ->where('id_observasi', $idObservasi)
            ->get()
            ->getResultArray();

        $existingMap = [];
        foreach ($existing as $record) {
            $existingMap[$record['id_kuk']] = $record;
        }

        $toUpdate = [];
        $toInsert = [];
        $timestamp = date('Y-m-d');

        foreach ($items as $idKuk => $item) {
            $data = [
                'kompeten' => $item['kompeten'],
                'keterangan' => $item['keterangan'],
                'tanggal_observasi' => $timestamp
            ];

            if (isset($existingMap[$idKuk])) {
                $toUpdate[] = array_merge($data, ['id' => $existingMap[$idKuk]['id']]);
            } else {
                $toInsert[] = array_merge($data, [
                    'id_observasi' => $idObservasi,
                    'id_kuk' => $idKuk
                ]);
            }
        }

        // Execute operations
        if (!empty($toUpdate)) {
            $this->db->table($detailTable)->updateBatch($toUpdate, 'id');
        }

        if (!empty($toInsert)) {
            $this->db->table($detailTable)->insertBatch($toInsert);
        }
    }

    /**
     * Get asesi list untuk skema tertentu dengan caching
     */
    public function getAsesiBySkema(int $idSkema): array
    {
        $cacheKey = "asesi_by_skema_{$idSkema}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $result = $this->observasiModel->getAsesiBySkema($idSkema);

            // Cache for 15 minutes
            $this->cache->save($cacheKey, $result, 900);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi by skema: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get observasi statistics untuk asesor dengan caching
     */
    public function getAsesorObservasiStats(int $idAsesor): array
    {
        $cacheKey = "asesor_observasi_stats_{$idAsesor}";

        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $stats = $this->db->table('observasi o')
                ->select('
                    COUNT(*) as total_observasi,
                    COUNT(CASE WHEN o.status = "completed" THEN 1 END) as completed_observasi,
                    COUNT(CASE WHEN o.status = "in_progress" THEN 1 END) as progress_observasi,
                    COUNT(CASE WHEN o.status = "draft" THEN 1 END) as draft_observasi,
                    ROUND(AVG(o.progress_percentage), 2) as avg_progress,
                    SUM(o.total_kuk) as total_kuk_assessed,
                    SUM(o.kompeten_count) as total_kompeten
                ')
                ->where('o.id_asesor', $idAsesor)
                ->get()
                ->getRowArray();

            if (!$stats) {
                $stats = [
                    'total_observasi' => 0,
                    'completed_observasi' => 0,
                    'progress_observasi' => 0,
                    'draft_observasi' => 0,
                    'avg_progress' => 0,
                    'total_kuk_assessed' => 0,
                    'total_kompeten' => 0
                ];
            }

            // Calculate competency rate
            $stats['competency_rate'] = $stats['total_kuk_assessed'] > 0 ?
                round(($stats['total_kompeten'] / $stats['total_kuk_assessed']) * 100, 2) : 0;

            // Cache for 5 minutes
            $this->cache->save($cacheKey, $stats, 300);

            return $stats;
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesor observasi statistics: ' . $e->getMessage());
            return [
                'total_observasi' => 0,
                'completed_observasi' => 0,
                'progress_observasi' => 0,
                'draft_observasi' => 0,
                'avg_progress' => 0,
                'total_kuk_assessed' => 0,
                'total_kompeten' => 0,
                'competency_rate' => 0
            ];
        }
    }

    /**
     * Save single KUK with optimized update
     */
    public function saveSingleKUK(int $idObservasi, int $idKuk, array $data): array
    {
        try {
            // Validate data
            if (!in_array($data['kompeten'], ['Y', 'N'])) {
                return [
                    'success' => false,
                    'message' => 'Nilai kompeten tidak valid'
                ];
            }

            $keterangan = isset($data['keterangan']) ?
                substr(trim(strip_tags($data['keterangan'])), 0, 1000) : '';

            // Check if record exists
            $existing = $this->db->table('detail_observasi')
                ->where('id_observasi', $idObservasi)
                ->where('id_kuk', $idKuk)
                ->get()
                ->getRowArray();

            $saveData = [
                'kompeten' => $data['kompeten'],
                'keterangan' => $keterangan,
                'tanggal_observasi' => date('Y-m-d')
            ];

            if ($existing) {
                // Update existing
                $this->db->table('detail_observasi')
                    ->where('id', $existing['id'])
                    ->update($saveData);
            } else {
                // Insert new
                $saveData['id_observasi'] = $idObservasi;
                $saveData['id_kuk'] = $idKuk;
                $this->db->table('detail_observasi')->insert($saveData);
            }

            // Update progress
            $this->updateObservasiProgress($idObservasi);

            // Clear caches
            $observasi = $this->db->table('observasi')->where('id_observasi', $idObservasi)->get()->getRowArray();
            if ($observasi) {
                $this->clearObservasiCaches($observasi['id_asesi'], $observasi['id_asesor']);
            }

            return [
                'success' => true,
                'message' => 'KUK berhasil disimpan'
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error saving single KUK: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyimpan KUK: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete observasi dengan cascade
     */
    public function deleteObservasi(int $idObservasi): array
    {
        $this->db->transStart();

        try {
            // Get observasi info for cache clearing
            $observasi = $this->db->table('observasi')
                ->where('id_observasi', $idObservasi)
                ->get()
                ->getRowArray();

            if (!$observasi) {
                return [
                    'success' => false,
                    'message' => 'Observasi tidak ditemukan'
                ];
            }

            // Delete details first
            $this->db->table('detail_observasi')
                ->where('id_observasi', $idObservasi)
                ->delete();

            // Delete main record
            $this->db->table('observasi')
                ->where('id_observasi', $idObservasi)
                ->delete();

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            // Clear caches
            $this->clearObservasiCaches($observasi['id_asesi'], $observasi['id_asesor']);

            return [
                'success' => true,
                'message' => 'Observasi berhasil dihapus'
            ];
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Error deleting observasi: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghapus observasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clear all observasi related caches (untuk admin)
     */
    public function clearAllCaches(): bool
    {
        try {
            $patterns = [
                'observasi_*',
                'kuk_structure_*',
                'asesi_by_skema_*',
                'asesor_observasi_stats_*'
            ];

            foreach ($patterns as $pattern) {
                $this->cache->deleteMatching($pattern);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error clearing all observasi caches: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get observasi progress report
     */
    public function getProgressReport(int $idAsesor, ?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $builder = $this->db->table('observasi o')
                ->select('
                    DATE(o.tanggal_observasi) as tanggal,
                    COUNT(*) as total_observasi,
                    SUM(o.total_kuk) as total_kuk,
                    SUM(o.kompeten_count) as total_kompeten,
                    ROUND(AVG(o.progress_percentage), 2) as rata_rata_progress
                ')
                ->where('o.id_asesor', $idAsesor);

            if ($dateFrom) {
                $builder->where('o.tanggal_observasi >=', $dateFrom);
            }

            if ($dateTo) {
                $builder->where('o.tanggal_observasi <=', $dateTo);
            }

            $report = $builder
                ->groupBy('DATE(o.tanggal_observasi)')
                ->orderBy('tanggal', 'DESC')
                ->get()
                ->getResultArray();

            return [
                'success' => true,
                'data' => $report
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting progress report: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil laporan progress: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
