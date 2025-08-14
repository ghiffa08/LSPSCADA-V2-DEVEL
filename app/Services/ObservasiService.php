<?php

namespace App\Services;

use App\Models\ObservasiModel;
use App\Models\DetailObservasiModel;
use App\Models\SkemaModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Cache\CacheInterface;

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
    protected CacheInterface $cache;

    public function __construct()
    {
        $this->observasiModel = new ObservasiModel();
        $this->detailModel = new DetailObservasiModel();
        $this->skemaModel = new SkemaModel();
        $this->db = \Config\Database::connect();
        $this->cache = \Config\Services::cache();
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
            log_message('error', 'ObservasiService validation error: ' . $validatedData['error']);
            return [
                'success' => false,
                'message' => $validatedData['error'],
                'data' => null
            ];
        }

        // Log input data for debugging
        log_message('info', 'ObservasiService saveObservation input: ' . json_encode($validatedData));

        $this->db->transStart();
        $id_observasi = null;

        try {
            // Validate foreign key constraints before attempting insert/update
            $this->validateForeignKeys($validatedData);

            // Prepare main observation data
            $observationData = [
                'id_asesor' => $validatedData['id_asesor'],
                'id_asesi' => $validatedData['id_asesi'],
                'id_pengajuan' => $validatedData['id_pengajuan'],
                'tanggal_observasi' => $validatedData['tanggal_observasi'],
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('info', 'ObservasiService observation data: ' . json_encode($observationData));

            // Check if observation already exists
            $existingObservation = $this->observasiModel
                ->where('id_asesi', $validatedData['id_asesi'])
                ->where('id_pengajuan', $validatedData['id_pengajuan'])
                ->where('tanggal_observasi', $validatedData['tanggal_observasi'])
                ->first();

            if ($existingObservation) {
                // Update existing observation
                $id_observasi = $existingObservation['id_observasi'];
                log_message('info', 'ObservasiService updating existing observation: ' . $id_observasi);

                $updateResult = $this->observasiModel->update($id_observasi, $observationData);
                if ($updateResult === false) {
                    $errors = $this->observasiModel->errors();
                    log_message('error', 'ObservasiModel update errors: ' . json_encode($errors));
                    throw new \Exception('Failed to update existing observation: ' . implode(', ', $errors));
                }
            } else {
                // Create new observation
                $observationData['created_at'] = date('Y-m-d H:i:s');
                $observationData['status'] = 'draft';

                log_message('info', 'ObservasiService creating new observation with data: ' . json_encode($observationData));
                $id_observasi = $this->observasiModel->insert($observationData);

                if ($id_observasi === false) {
                    $errors = $this->observasiModel->errors();
                    log_message('error', 'ObservasiModel insert errors: ' . json_encode($errors));
                    throw new \Exception('Failed to create new observation: ' . implode(', ', $errors));
                }
            }

            if (!$id_observasi) {
                throw new \Exception('Failed to get observation ID');
            }

            log_message('info', 'ObservasiService observation ID: ' . $id_observasi);

            // Enhanced batch upsert for details
            if (isset($validatedData['details']) && !empty($validatedData['details'])) {
                log_message('info', 'ObservasiService processing details: ' . count($validatedData['details']) . ' items');
                $this->optimizedUpsertDetails($id_observasi, $validatedData['details']);
            }

            // Update progress and status
            log_message('info', 'ObservasiService updating progress');
            $this->updateObservasiProgress($id_observasi);

            // Check transaction status before completing
            $transStatus = $this->db->transStatus();
            log_message('info', 'ObservasiService transaction status before complete: ' . ($transStatus ? 'true' : 'false'));

            $this->db->transComplete();

            $finalTransStatus = $this->db->transStatus();
            log_message('info', 'ObservasiService final transaction status: ' . ($finalTransStatus ? 'true' : 'false'));

            if ($finalTransStatus === false) {
                $error = $this->db->error();
                $errorMessage = 'Unknown database error';

                if (!empty($error['message'])) {
                    $errorMessage = $error['message'];
                } elseif (!empty($error['code'])) {
                    $errorMessage = "Database error code: " . $error['code'];
                }

                log_message('error', 'ObservasiService transaction failed - DB Error: ' . json_encode($error));
                log_message('error', 'ObservasiService last query: ' . $this->db->getLastQuery());

                throw new \Exception('Transaction failed: ' . $errorMessage);
            }

            log_message('info', 'ObservasiService transaction completed successfully');

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
            // Ensure transaction is rolled back
            if ($this->db->transStatus() !== false) {
                $this->db->transRollback();
            }

            log_message('error', 'ObservasiService saveObservation Error: ' . $e->getMessage());
            log_message('error', 'ObservasiService saveObservation Stack trace: ' . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Gagal menyimpan observasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get observation with details using eager loading - NO CACHE
     * Always fetch fresh data from database
     */
    public function getObservationWithDetails(int $id_observasi): array
    {
        try {
            // PERBAIKAN: Query dengan relasi yang benar sesuai struktur database
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
                ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'inner') // PERBAIKAN: JOIN ke asesmen
                ->join('skema', 'skema.id_skema = asm.id_skema', 'inner') // PERBAIKAN: JOIN dari asesmen ke skema
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

            // Get summary - PERBAIKAN: calculation langsung tanpa cache
            $summary = $this->calculateObservationSummary($id_observasi);

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

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'ObservasiService getObservationWithDetails Error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Gagal mengambil data observasi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Calculate observation summary langsung dari database
     */
    private function calculateObservationSummary(int $idObservasi): array
    {
        try {
            // Get summary langsung dari database tanpa cache
            $summary = $this->db->table('detail_observasi do')
                ->select('
                    COUNT(*) as total_kuk,
                    COUNT(CASE WHEN do.kompeten = "Y" THEN 1 END) as kompeten_count,
                    COUNT(CASE WHEN do.kompeten = "N" THEN 1 END) as tidak_kompeten_count
                ')
                ->where('do.id_observasi', $idObservasi)
                ->get()
                ->getRowArray();

            if (!$summary) {
                return [
                    'total_kuk' => 0,
                    'kompeten_count' => 0,
                    'tidak_kompeten_count' => 0,
                    'progress_percentage' => 0,
                    'status' => 'draft'
                ];
            }

            // Calculate progress percentage
            $progress = $summary['total_kuk'] > 0 ?
                ($summary['kompeten_count'] / $summary['total_kuk']) * 100 : 0;

            // Determine status
            $status = 'draft';
            if ($progress > 0 && $progress < 100) {
                $status = 'in_progress';
            } elseif ($progress >= 100) {
                $status = 'completed';
            }

            $summary['progress_percentage'] = round($progress, 2);
            $summary['status'] = $status;

            return $summary;
        } catch (\Exception $e) {
            log_message('error', 'Error calculating observation summary: ' . $e->getMessage());
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
     * Group details by unit untuk display yang lebih baik
     */
    private function groupDetailsByUnit(array $details): array
    {
        $grouped = [];

        foreach ($details as $detail) {
            $unitKey = $detail['kode_unit'];

            if (!isset($grouped[$unitKey])) {
                $grouped[$unitKey] = [
                    'kode_unit' => $detail['kode_unit'],
                    'nama_unit' => $detail['nama_unit'],
                    'elements' => []
                ];
            }

            $elemenKey = $detail['kode_elemen'];

            if (!isset($grouped[$unitKey]['elements'][$elemenKey])) {
                $grouped[$unitKey]['elements'][$elemenKey] = [
                    'kode_elemen' => $detail['kode_elemen'],
                    'nama_elemen' => $detail['nama_elemen'],
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
     * Get KUK structure for observation form - NO CACHE
     * Always return fresh data from database
     */
    public function getKukStructureForSchema(int $id_skema, string $id_asesi, ?int $id_observasi = null): array
    {
        try {
            // PERBAIKAN: Build structure dengan query yang benar
            $result = $this->buildKukStructure($id_skema, $id_asesi, $id_observasi);

            // Get existing observasi data if id_observasi provided
            $existingData = [];
            if ($id_observasi) {
                $existingData = $this->getExistingObservasiData($id_asesi, $id_skema, $id_observasi);
            }

            return [
                'success' => true,
                'observasi' => $result['observasi'],
                'existing_data' => $existingData,
                'totalKUK' => $result['totalKUK'],
                'id_observasi' => $id_observasi
            ];
        } catch (\Exception $e) {
            log_message('error', 'ObservasiService::getKukStructureForSchema error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memuat struktur observasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Build KUK structure dengan relasi yang benar
     */
    private function buildKukStructure(int $id_skema, string $id_asesi, ?int $id_observasi = null): array
    {
        // Query untuk mengambil struktur KUK dengan relasi yang benar
        $sql = "
            SELECT 
                COALESCE(kk.id_kelompok, 1) as id_kelompok,
                COALESCE(kk.nama_kelompok, 'Kelompok Utama') as nama_kelompok,
                u.id_unit,
                u.kode_unit,
                u.nama_unit,
                e.id_elemen,
                e.kode_elemen,
                e.nama_elemen,
                k.id_kuk,
                k.kode_kuk,
                k.nama_kuk as kriteria_unjuk_kerja
            FROM skema s
            INNER JOIN unit u ON u.id_skema = s.id_skema AND u.status = 'Y'
            LEFT JOIN kelompok_unit ku ON ku.id_unit = u.id_unit
            LEFT JOIN kelompok_kerja kk ON kk.id_kelompok = ku.id_kelompok AND kk.id_skema = s.id_skema
            LEFT JOIN elemen e ON e.id_unit = u.id_unit
            LEFT JOIN kuk k ON k.id_elemen = e.id_elemen
            WHERE s.id_skema = ? AND s.status = 'Y'
            ORDER BY 
                COALESCE(kk.id_kelompok, 1), 
                u.kode_unit, 
                e.kode_elemen, 
                k.kode_kuk
        ";

        $rawData = $this->db->query($sql, [$id_skema])->getResultArray();

        if (empty($rawData)) {
            throw new \Exception('Tidak ada unit kompetensi ditemukan untuk skema ini');
        }

        // Transform ke hierarchical structure
        $structuredData = $this->transformToHierarchicalStructure($rawData);

        return [
            'observasi' => $structuredData,
            'totalKUK' => count(array_filter($rawData, function ($item) {
                return !empty($item['id_kuk']);
            }))
        ];
    }

    /**
     * Transform flat data to hierarchical structure
     */
    private function transformToHierarchicalStructure(array $rawData): array
    {
        $structure = [];
        $unitTracker = [];
        $elemenTracker = [];

        foreach ($rawData as $row) {
            $kelompokId = $row['id_kelompok'] ?? 1;
            $unitId = $row['id_unit'] ?? 0;
            $elemenId = $row['id_elemen'] ?? 0;

            // Initialize kelompok if not exists
            if (!isset($structure[$kelompokId])) {
                $structure[$kelompokId] = [
                    'id_kelompok' => $kelompokId,
                    'nama_kelompok' => $row['nama_kelompok'] ?? 'Kelompok Utama',
                    'units' => []
                ];
            }

            // Initialize unit if not exists and unitId is valid
            if ($unitId && !isset($structure[$kelompokId]['units'][$unitId])) {
                $structure[$kelompokId]['units'][$unitId] = [
                    'id_unit' => $row['id_unit'],
                    'kode_unit' => $row['kode_unit'],
                    'nama_unit' => $row['nama_unit'],
                    'elements' => []
                ];
                $unitTracker[$unitId] = true;
            }

            // Add elemen if exists and not already added
            if ($elemenId && $unitId && !isset($structure[$kelompokId]['units'][$unitId]['elements'][$elemenId])) {
                $structure[$kelompokId]['units'][$unitId]['elements'][$elemenId] = [
                    'id_elemen' => $row['id_elemen'],
                    'kode_elemen' => $row['kode_elemen'],
                    'nama_elemen' => $row['nama_elemen'],
                    'kuks' => []
                ];
                $elemenTracker[$elemenId] = true;
            }

            // Add KUK if exists
            if (!empty($row['id_kuk']) && $elemenId && $unitId) {
                $structure[$kelompokId]['units'][$unitId]['elements'][$elemenId]['kuks'][] = [
                    'id_kuk' => $row['id_kuk'],
                    'kode_kuk' => $row['kode_kuk'],
                    'nama_kuk' => $row['kriteria_unjuk_kerja']
                ];
            }
        }

        return $structure;
    }

    /**
     * Get existing observasi data
     */
    private function getExistingObservasiData(string $id_asesi, int $id_skema, ?int $id_observasi = null): array
    {
        if (!$id_observasi) {
            return [];
        }

        $existing = $this->db->table('detail_observasi do')
            ->select('do.id_kuk, do.kompeten, do.keterangan')
            ->join('observasi o', 'o.id_observasi = do.id_observasi', 'inner')
            ->where('do.id_observasi', $id_observasi)
            ->get()
            ->getResultArray();

        $existingData = [];
        foreach ($existing as $item) {
            $existingData[$item['id_kuk']] = [
                'kompeten' => $item['kompeten'],
                'keterangan' => $item['keterangan']
            ];
        }

        return $existingData;
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
                    'skema.nama_skema',
                    'skema.kode_skema',
                    'skema.id_skema'
                ])
                ->join('asesi', 'asesi.id_asesi = observasi.id_asesi', 'inner')
                ->join('users as asesi_user', 'asesi_user.id = asesi.id_user', 'inner')
                ->join('asesor', 'asesor.id_asesor = observasi.id_asesor', 'inner')
                ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
                ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = observasi.id_pengajuan', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'inner') // PERBAIKAN: JOIN ke asesmen
                ->join('skema', 'skema.id_skema = asm.id_skema', 'inner'); // PERBAIKAN: JOIN dari asesmen ke skema

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
     * Validate foreign key constraints before save
     */
    private function validateForeignKeys(array $data): void
    {
        // Check if asesor exists
        $asesor = $this->db->table('asesor')->where('id_asesor', $data['id_asesor'])->get()->getRowArray();
        if (!$asesor) {
            throw new \Exception("Asesor dengan ID {$data['id_asesor']} tidak ditemukan");
        }

        // Check if asesi exists
        $asesi = $this->db->table('asesi')->where('id_asesi', $data['id_asesi'])->get()->getRowArray();
        if (!$asesi) {
            throw new \Exception("Asesi dengan ID {$data['id_asesi']} tidak ditemukan");
        }

        // Check if pengajuan exists
        $pengajuan = $this->db->table('pengajuan_asesmen')->where('id_pengajuan', $data['id_pengajuan'])->get()->getRowArray();
        if (!$pengajuan) {
            throw new \Exception("Pengajuan asesmen dengan ID {$data['id_pengajuan']} tidak ditemukan");
        }

        // Validate that pengajuan belongs to the asesi
        if ($pengajuan['id_asesi'] !== $data['id_asesi']) {
            throw new \Exception("Pengajuan asesmen tidak sesuai dengan asesi yang dipilih");
        }

        log_message('info', 'ObservasiService foreign key validation passed');
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

        // PERBAIKAN: Get KUK details dengan JOIN ke skema
        $kukIds = array_column($details, 'id_kuk');
        $kukDetails = $this->db->table('kuk k')
            ->select('k.id_kuk, u.id_skema')
            ->join('elemen e', 'e.id_elemen = k.id_elemen', 'inner')
            ->join('unit u', 'u.id_unit = e.id_unit', 'inner')
            ->whereIn('k.id_kuk', $kukIds)
            ->get()
            ->getResultArray();

        $kukSkemaMap = [];
        foreach ($kukDetails as $kuk) {
            $kukSkemaMap[$kuk['id_kuk']] = $kuk['id_skema'];
        }

        $toInsert = [];
        $toUpdate = [];
        $timestamp = date('Y-m-d');

        foreach ($details as $detail) {
            // Skip if we can't find the id_skema for this KUK
            if (!isset($kukSkemaMap[$detail['id_kuk']])) {
                log_message('warning', "KUK ID {$detail['id_kuk']} not found or missing id_skema, skipping");
                continue;
            }

            $detailData = [
                'kompeten' => $detail['kompeten'],
                'keterangan' => $detail['keterangan'] ?? '',
                'tanggal_observasi' => $detail['tanggal_observasi'] ?? $timestamp
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
                    'id_kuk' => $detail['id_kuk'],
                    'id_skema' => $kukSkemaMap[$detail['id_kuk']]
                ]);
            }
        }

        // Execute batch operations
        if (!empty($toUpdate)) {
            log_message('info', 'ObservasiService updating ' . count($toUpdate) . ' detail records');
            $updateResult = $this->db->table('detail_observasi')->updateBatch($toUpdate, 'id');
            if ($updateResult === false) {
                $error = $this->db->error();
                log_message('error', 'Failed to update detail_observasi batch: ' . json_encode($error));
                throw new \Exception('Failed to update detail observasi: ' . ($error['message'] ?? 'Unknown error'));
            }
        }

        if (!empty($toInsert)) {
            log_message('info', 'ObservasiService inserting ' . count($toInsert) . ' detail records');
            $insertResult = $this->db->table('detail_observasi')->insertBatch($toInsert);
            if ($insertResult === false) {
                $error = $this->db->error();
                log_message('error', 'Failed to insert detail_observasi batch: ' . json_encode($error));
                throw new \Exception('Failed to insert detail observasi: ' . ($error['message'] ?? 'Unknown error'));
            }
        }

        log_message('info', 'ObservasiService optimizedUpsertDetails completed successfully');
    }

    /**
     * Update observasi progress and status - PERBAIKAN
     */
    private function updateObservasiProgress(int $idObservasi): void
    {
        try {
            log_message('info', "ObservasiService updateObservasiProgress for ID: {$idObservasi}");

            // Get fresh stats dari detail_observasi
            $stats = $this->db->table('detail_observasi')
                ->select('
                    COUNT(*) as total_kuk,
                    COUNT(CASE WHEN kompeten = "Y" THEN 1 END) as kompeten_count,
                    COUNT(CASE WHEN kompeten = "N" THEN 1 END) as tidak_kompeten_count
                ')
                ->where('id_observasi', $idObservasi)
                ->get()
                ->getRowArray();

            log_message('info', 'ObservasiService stats: ' . json_encode($stats));

            $progress = 0;
            if ($stats && $stats['total_kuk'] > 0) {
                $progress = ($stats['kompeten_count'] / $stats['total_kuk']) * 100;
            }

            // Determine status
            $status = 'draft';
            if ($progress > 0 && $progress < 100) {
                $status = 'in_progress';
            } elseif ($progress >= 100) {
                $status = 'completed';
            }

            $updateData = [
                'total_kuk' => $stats['total_kuk'] ?? 0,
                'kompeten_count' => $stats['kompeten_count'] ?? 0,
                'progress_percentage' => round($progress, 2),
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            log_message('info', 'ObservasiService updating observasi with data: ' . json_encode($updateData));

            $updateResult = $this->db->table('observasi')
                ->where('id_observasi', $idObservasi)
                ->update($updateData);

            if ($updateResult === false) {
                $error = $this->db->error();
                log_message('error', 'Failed to update observasi progress: ' . json_encode($error));
                throw new \Exception('Failed to update observasi progress: ' . ($error['message'] ?? 'Unknown error'));
            }

            log_message('info', 'ObservasiService progress updated successfully');
        } catch (\Exception $e) {
            log_message('error', 'Error updating observasi progress: ' . $e->getMessage());
            throw $e;
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
            // Clear specific caches - since deleteMatching doesn't exist, we clear individual keys
            $keysToDelete = [
                "observasi_structure_asesi_{$idAsesi}",
                "observasi_structure_kuk_{$idAsesi}",
                "observasi_summary_{$idAsesi}",
                "observasi_summary_{$idAsesor}",
                "asesor_stats_{$idAsesor}",
                "asesi_by_skema_{$idAsesor}",
                "kuk_structure_asesi_{$idAsesi}"
            ];

            foreach ($keysToDelete as $key) {
                $this->cache->delete($key);
            }
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

        // Get KUK details to get id_skema for each KUK
        $kukIds = array_keys($items);
        $kukDetails = $this->db->table('kuk')
            ->select('id_kuk, id_skema')
            ->whereIn('id_kuk', $kukIds)
            ->get()
            ->getResultArray();

        $kukSkemaMap = [];
        foreach ($kukDetails as $kuk) {
            $kukSkemaMap[$kuk['id_kuk']] = $kuk['id_skema'];
        }

        $toUpdate = [];
        $toInsert = [];
        $timestamp = date('Y-m-d');

        foreach ($items as $idKuk => $item) {
            // Skip if we can't find the id_skema for this KUK
            if (!isset($kukSkemaMap[$idKuk])) {
                log_message('warning', "KUK ID {$idKuk} not found or missing id_skema, skipping");
                continue;
            }

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
                    'id_kuk' => $idKuk,
                    'id_skema' => $kukSkemaMap[$idKuk]
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
                // Get id_skema for this KUK
                $kukDetail = $this->db->table('kuk')
                    ->select('id_skema')
                    ->where('id_kuk', $idKuk)
                    ->get()
                    ->getRowArray();

                if (!$kukDetail) {
                    return [
                        'success' => false,
                        'message' => 'KUK tidak ditemukan'
                    ];
                }

                // Insert new
                $saveData['id_observasi'] = $idObservasi;
                $saveData['id_kuk'] = $idKuk;
                $saveData['id_skema'] = $kukDetail['id_skema'];
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
            // Since deleteMatching doesn't exist, we'll use clean() to clear all cache
            // or manually delete known cache keys
            $knownCacheKeys = [
                'observasi_structure_asesi_',
                'observasi_structure_kuk_',
                'observasi_summary_',
                'kuk_structure_',
                'asesi_by_skema_',
                'asesor_observasi_stats_'
            ];

            // For production, we could implement a more sophisticated approach
            // For now, we'll clear the entire cache as it's safer
            $this->cache->clean();

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
