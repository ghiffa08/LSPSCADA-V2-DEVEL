<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class ObservasiModel extends Model
{
    use DataTableTrait;

    protected $table            = 'observasi';
    protected $primaryKey       = 'id_observasi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesor',
        'id_apl1',  // Changed from id_asesi to id_apl1
        'tanggal_observasi'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['observasi.id_asesor', 'apl1.nama_siswa', 'apl1.nik'];

    /**
     * Apply joins for DataTable query - Updated for APL1
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('apl1', 'apl1.id_apl1 = observasi.id_apl1', 'inner')
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner')
            ->join('asesor', 'asesor.id_asesor = observasi.id_asesor', 'inner')
            ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner')
               ->join('tuk', 'tuk.id_tuk = asesmen.id_tuk', 'inner');;
    }

    /**
     * Apply custom select fields for DataTable query - Updated for APL1
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'observasi.*, 
            asesor_user.nama_lengkap AS nama_asesor, 
            apl1.nama_siswa AS nama_asesi,
            apl1.nik,
            apl1.email,
            skema.nama_skema,
            tuk.nama_tuk,
            apl1.validasi_apl1 as status_pengajuan'
        );
    }

    /**
     * Transform DataTable results if needed
     *
     * @param array $data Result data
     * @return array
     */
    protected function transformDataTableResults($data)
    {
        // You can transform data here if needed
        // For example, format dates, calculate values, etc.
        return $data;
    }

    /**
     * Get asesi data for the given assessment ID - Updated for APL1
     */
    public function getAsesiBySkema($id_skema)
    {
        $builder = $this->db->table('apl1');
        $builder->select('
            apl1.id_apl1, 
            apl1.nama_siswa as nama_lengkap,
            apl1.validasi_apl1 as status_pengajuan
        ');
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->where('asesmen.id_skema', $id_skema);
        $builder->where('apl1.validasi_apl1', 'validated'); // Only validated applications

        return $builder->get()->getResultArray();
    }

    /**
     * Get observation structure data for a specific schema - OPTIMIZED VERSION
     * Uses single query with proper indexing and caching
     *
     * @param int $id_skema Schema ID
     * @return array Hierarchically structured data
     */
    public function getStrukturObservasiSkema(int $id_skema): array
    {
        $cacheKey = "struktur_skema_{$id_skema}";

        // Try to get from cache first (data rarely changes)
        if ($cached = cache($cacheKey)) {
            return $cached;
        }

        // Use raw SQL to avoid issues with COALESCE in ORDER BY
        $sql = "
        SELECT 
            s.id_skema,
            s.kode_skema,
            s.nama_skema,
            s.jenis_skema,
            COALESCE(kk.id_kelompok, 1) as id_kelompok,
            COALESCE(kk.nama_kelompok, 'Kelompok 1') as nama_kelompok,
            u.id_unit,
            u.kode_unit,
            u.nama_unit,
            e.id_elemen,
            e.kode_elemen,
            e.nama_elemen,
            k.id_kuk,
            k.kode_kuk,
          k.pertanyaan AS kriteria_unjuk_kerja,
            CONCAT(u.kode_unit, '.', e.kode_elemen, '.', k.kode_kuk) as hierarchy_path
        FROM skema s
        INNER JOIN unit u ON u.id_skema = s.id_skema AND u.status = 'Y'
        LEFT JOIN kelompok_unit ku ON ku.id_unit = u.id_unit
        LEFT JOIN kelompok_kerja kk ON kk.id_kelompok = ku.id_kelompok AND kk.id_skema = s.id_skema
        LEFT JOIN elemen e ON e.id_unit = u.id_unit
        LEFT JOIN kuk k ON k.id_elemen = e.id_elemen
        WHERE s.id_skema = ? AND s.status = 'Y'
        ORDER BY COALESCE(kk.id_kelompok, 1), u.kode_unit, e.kode_elemen, k.kode_kuk
        ";

        $rawData = $this->db->query($sql, [$id_skema])->getResultArray();

        // Transform flat data into hierarchical structure for better frontend performance
        $structuredData = $this->transformToHierarchicalStructure($rawData);

        // Cache for 1 hour (data doesn't change frequently)
        cache()->save($cacheKey, $structuredData, 3600);

        return $structuredData;
    }

    /**
     * Transform flat query result into hierarchical structure
     * Eliminates need for multiple loops in frontend
     */
    private function transformToHierarchicalStructure(array $rawData): array
    {
        $structure = [
            'skema' => null,
            'kelompok_kerja' => [],
            'statistics' => [
                'total_units' => 0,
                'total_elemen' => 0,
                'total_kuk' => 0
            ]
        ];

        $unitTracker = [];
        $elemenTracker = [];

        foreach ($rawData as $row) {
            // Set skema info once
            if (!$structure['skema']) {
                $structure['skema'] = [
                    'id_skema' => $row['id_skema'] ?? null,
                    'kode_skema' => $row['kode_skema'] ?? '',
                    'nama_skema' => $row['nama_skema'] ?? '',
                    'jenis_skema' => $row['jenis_skema'] ?? ''
                ];
            }

            $kelompokId = $row['id_kelompok'] ?? 0;
            $unitId = $row['id_unit'] ?? 0;
            $elemenId = $row['id_elemen'] ?? 0;

            // Initialize kelompok if not exists
            if (!isset($structure['kelompok_kerja'][$kelompokId])) {
                $structure['kelompok_kerja'][$kelompokId] = [
                    'id_kelompok' => $kelompokId,
                    'nama_kelompok' => $row['nama_kelompok'] ?? 'Kelompok Tidak Diketahui',
                    'units' => []
                ];
            }

            // Initialize unit if not exists and unitId is valid
            if ($unitId && !isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId] = [
                    'id_unit' => $unitId,
                    'kode_unit' => $row['kode_unit'] ?? '',
                    'nama_unit' => $row['nama_unit'] ?? '',
                    'elemen' => []
                ];
                $unitTracker[$unitId] = true;
            }

            // Add elemen if exists and not already added
            if ($elemenId && $unitId && !isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId] = [
                    'id_elemen' => $elemenId,
                    'kode_elemen' => $row['kode_elemen'] ?? '',
                    'nama_elemen' => $row['nama_elemen'] ?? '',
                    'kuk' => []
                ];
                $elemenTracker[$elemenId] = true;
            }

            // Add KUK if exists
            if (!empty($row['id_kuk']) && $elemenId && $unitId) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][] = [
                    'id_kuk' => $row['id_kuk'],
                    'kode_kuk' => $row['kode_kuk'] ?? '',
                    'kriteria_unjuk_kerja' => $row['kriteria_unjuk_kerja'] ?? '',
                    'hierarchy_path' => $row['hierarchy_path'] ?? ''
                ];
                $structure['statistics']['total_kuk']++;
            }
        }

        // Calculate statistics
        $structure['statistics']['total_units'] = count($unitTracker);
        $structure['statistics']['total_elemen'] = count($elemenTracker);

        // Convert associative arrays to indexed arrays for easier iteration
        $structure['kelompok_kerja'] = array_values($structure['kelompok_kerja']);
        foreach ($structure['kelompok_kerja'] as &$kelompok) {
            $kelompok['units'] = array_values($kelompok['units']);
            foreach ($kelompok['units'] as &$unit) {
                $unit['elemen'] = array_values($unit['elemen']);
            }
        }

        return $structure;
    }

    /**
     * Get observation schema structure by observation ID - Updated for APL1
     *
     * @param int $id_observasi Observation ID
     * @return array
     */
    public function getStrukturById(int $id_observasi): array
    {
        // Get the schema ID from the observation with correct relations
        $observasiBuilder = $this->db->table('observasi o');
        $observasiBuilder->select('asesmen.id_skema');
        $observasiBuilder->join('apl1', 'apl1.id_apl1 = o.id_apl1', 'inner');
        $observasiBuilder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $observasiBuilder->where('o.id_observasi', $id_observasi);

        $observasiResult = $observasiBuilder->get()->getRowArray();

        if (!$observasiResult) {
            return [];
        }

        $id_skema = $observasiResult['id_skema'];

        // Now use the schema ID to get the structure
        return $this->getStrukturObservasiSkema($id_skema);
    }

    /**
     * Get observation metadata including assessee data - Updated for APL1
     *
     * @param string $id_apl1 APL1 ID
     * @param int $id_skema Schema ID
     * @return array|null
     */
    public function getObservasiData(string $id_apl1, int $id_skema): ?array
    {
        $builder = $this->db->table('observasi');
        $builder->select([
            'observasi.*',
            'apl1.nik',
            'apl1.nama_siswa as nama_asesi',
            'apl1.email as email_asesi',
            'asesor_user.nama_lengkap as nama_asesor',
            'asesor_user.email as email_asesor',
            'skema.nama_skema',
            'skema.kode_skema'
        ]);
        // Join tables with correct relations for APL1
        $builder->join('apl1', 'apl1.id_apl1 = observasi.id_apl1', 'inner');
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->join('asesor', 'asesor.id_asesor = observasi.id_asesor', 'inner');
        $builder->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');

        // Apply filters
        $builder->where('observasi.id_apl1', $id_apl1);
        $builder->where('asesmen.id_skema', $id_skema);

        return $builder->get()->getRowArray();
    }

    /**
     * Get observation metadata by observation ID - Updated for APL1
     *
     * @param int $id_observasi Observation ID
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $builder = $this->db->table('observasi');
        $builder->select([
            'observasi.*',
            'apl1.nik',
            'apl1.nama_siswa as nama_asesi',
            'apl1.email as email_asesi',
            'asesor_user.nama_lengkap as nama_asesor',
            'asesor_user.email as email_asesor',
            'skema.nama_skema',
            'skema.kode_skema'
        ]);
        // Join tables with correct relations for APL1
        $builder->join('apl1', 'apl1.id_apl1 = observasi.id_apl1', 'inner');
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->join('asesor', 'asesor.id_asesor = observasi.id_asesor', 'inner');
        $builder->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');

        // Filter by observation ID
        $builder->where('observasi.id_observasi', $id);

        return $builder->get()->getRowArray();
    }

    /**
     * Get existing observation data for a specific assessee - Updated for APL1
     *
     * @param string $id_apl1 APL1 ID
     * @return array
     */
    public function getExistingObservasi(string $id_apl1): array
    {
        $builder = $this->db->table('detail_observasi');
        $builder->select('detail_observasi.id_kuk, detail_observasi.kompeten, detail_observasi.keterangan');
        $builder->join('observasi', 'observasi.id_observasi = detail_observasi.id_observasi', 'inner');
        $builder->where('observasi.id_apl1', $id_apl1);

        $result = $builder->get()->getResultArray();

        // Format data as associative array with id_kuk as key
        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['id_kuk']] = [
                'kompeten' => $row['kompeten'],
                'keterangan' => $row['keterangan']
            ];
        }

        return $formatted;
    }

    /**
     * Get existing observation data for a specific observation
     *
     * @param int $id_observasi Observation ID
     * @return array
     */
    public function getExistingById(int $id_observasi): array
    {
        $builder = $this->db->table('detail_observasi');
        $builder->select('detail_observasi.id_kuk, detail_observasi.kompeten, detail_observasi.keterangan');
        $builder->where('detail_observasi.id_observasi', $id_observasi);

        $result = $builder->get()->getResultArray();

        // Format data as associative array with id_kuk as key
        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['id_kuk']] = [
                'kompeten' => $row['kompeten'],
                'keterangan' => $row['keterangan']
            ];
        }

        return $formatted;
    }

    /**
     * Get work groups with units for a scheme based on observation ID - Updated for APL1
     * 
     * @param int $id_observasi Observation ID
     * @return array
     */
    public function getWorkGroupsWithUnitsById(int $id_observasi): array
    {
        // Get the schema ID related to this observation with correct relations
        $observasiQuery = $this->db->table('observasi o')
            ->select('asesmen.id_skema')
            ->join('apl1', 'apl1.id_apl1 = o.id_apl1', 'inner')
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner')
            ->where('o.id_observasi', $id_observasi)
            ->get()
            ->getRowArray();

        if (!$observasiQuery) {
            return [];
        }

        $id_skema = $observasiQuery['id_skema'];

        // Use raw SQL to avoid issues with COALESCE in orderBy
        $sql = "
        SELECT 
            COALESCE(kk.id_kelompok, 1) as id_kelompok,
            COALESCE(kk.nama_kelompok, 'Kelompok Utama') as nama_kelompok,
            u.id_unit,
            u.kode_unit,
            u.nama_unit,
            u.nama_unit as judul_unit
        FROM unit u
        LEFT JOIN kelompok_unit ku ON ku.id_unit = u.id_unit
        LEFT JOIN kelompok_kerja kk ON kk.id_kelompok = ku.id_kelompok AND kk.id_skema = u.id_skema
        WHERE u.id_skema = ? AND u.status = 'Y'
        ORDER BY COALESCE(kk.id_kelompok, 1), u.kode_unit
        ";

        $result = $this->db->query($sql, [$id_skema])->getResultArray();

        $groupedData = [];
        foreach ($result as $row) {
            $kelompokId = $row['id_kelompok'];

            if (!isset($groupedData[$kelompokId])) {
                $groupedData[$kelompokId] = [
                    'id_kelompok' => $kelompokId,
                    'nama_kelompok' => $row['nama_kelompok'],
                    'units' => []
                ];
            }

            $groupedData[$kelompokId]['units'][] = [
                'id_unit' => $row['id_unit'],
                'kode_unit' => $row['kode_unit'],
                'nama_unit' => $row['nama_unit'],
                'judul_unit' => $row['judul_unit']
            ];
        }

        return array_values($groupedData);
    }

    /**
     * Save observation data with details - Updated for APL1
     * Unified method to handle different types of observation saves
     * 
     * @param array $masterData Master observation data
     * @param array|null $detailData Detail observation data
     * @param bool $singleKUK Whether this is a single KUK save operation
     * @return bool|int Returns inserted ID on success or boolean success status
     */
    public function saveObservasiData(array $masterData, ?array $detailData = null, bool $singleKUK = false)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // Get or create the master observation record
            $id_observasi = $masterData['id_observasi'] ?? null;

            if (!$id_observasi) {
                // Check if there's an existing record
                $existing = $db->table($this->table)
                    ->where('id_asesor', $masterData['id_asesor'])
                    ->where('id_apl1', $masterData['id_apl1']) // Changed from id_asesi to id_apl1
                    ->where('tanggal_observasi', $masterData['tanggal_observasi'])
                    ->get()
                    ->getRow();

                if ($existing) {
                    $id_observasi = $existing->id_observasi;
                    $db->table($this->table)
                        ->where('id_observasi', $id_observasi)
                        ->update($masterData);
                } else {
                    $db->table($this->table)->insert($masterData);
                    $id_observasi = $db->insertID();
                }
            } else {
                $db->table($this->table)
                    ->where('id_observasi', $id_observasi)
                    ->update($masterData);
            }

            // Process detail data if provided
            if ($detailData) {
                $id_skema = $detailData['id_skema'];

                if ($singleKUK) {
                    // Single KUK save
                    $this->saveSingleDetailKUK($id_observasi, $detailData);
                } else if (isset($detailData['items'])) {
                    // Batch items save
                    $this->saveBatchDetailKUK($id_observasi, $id_skema, $detailData['items'], $masterData['tanggal_observasi']);
                } else if (isset($detailData['kuk'])) {
                    // Handle bulk save (clear and re-insert)
                    $this->saveBulkDetailKUK($id_observasi, $id_skema, $detailData['kuk'], $detailData['keterangan'], $masterData['tanggal_observasi']);
                }
            }

            $db->transComplete();
            return $id_observasi;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in saveObservasiData: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Helper method to save a single KUK detail
     */
    private function saveSingleDetailKUK($id_observasi, $data)
    {
        $table = 'detail_observasi';
        $db = $this->db;

        // Check if this record already exists
        $existing = $db->table($table)
            ->where('id_observasi', $id_observasi)
            ->where('id_skema', $data['id_skema'])
            ->where('id_kuk', $data['id_kuk'])
            ->get()
            ->getRowArray();

        $detailData = [
            'id_observasi' => $id_observasi,
            'id_skema' => $data['id_skema'],
            'id_kuk' => $data['id_kuk'],
            'kompeten' => $data['kompeten'],
            'keterangan' => $data['keterangan'],
            'tanggal_observasi' => $data['tanggal_observasi']
        ];

        if ($existing) {
            // Update existing record
            return $db->table($table)
                ->where('id_observasi', $id_observasi)
                ->where('id_skema', $data['id_skema'])
                ->where('id_kuk', $data['id_kuk'])
                ->update($detailData);
        } else {
            // Generate ID for new record if needed
            $lastId = $db->table($table)->selectMax('id')->get()->getRowArray()['id'] ?? 0;
            $detailData['id'] = $lastId + 1;

            return $db->table($table)->insert($detailData);
        }
    }

    /**
     * Helper method to save batch KUK details
     */
    private function saveBatchDetailKUK($id_observasi, $id_skema, $items, $tanggal_observasi)
    {
        $table = 'detail_observasi';
        $db = $this->db;

        // Get existing records for faster lookup
        $existingRecords = $db->table($table)
            ->where('id_observasi', $id_observasi)
            ->where('id_skema', $id_skema)
            ->get()
            ->getResultArray();

        // Create lookup map
        $existingMap = [];
        foreach ($existingRecords as $record) {
            $existingMap[$record['id_kuk']] = $record;
        }

        // Prepare arrays for operations
        $dataToInsert = [];

        // Get current max ID for new records
        $lastId = $db->table($table)->selectMax('id')->get()->getRowArray()['id'] ?? 0;
        $nextId = $lastId + 1;

        // Process each item
        foreach ($items as $id_kuk => $item) {
            if (isset($existingMap[$id_kuk])) {
                // Update existing record
                $db->table($table)
                    ->where('id_observasi', $id_observasi)
                    ->where('id_skema', $id_skema)
                    ->where('id_kuk', $id_kuk)
                    ->update([
                        'kompeten' => $item['kompeten'],
                        'keterangan' => $item['keterangan'],
                        'tanggal_observasi' => $tanggal_observasi
                    ]);
            } else {
                // Prepare data for insertion
                $dataToInsert[] = [
                    'id' => $nextId++,
                    'id_observasi' => $id_observasi,
                    'id_skema' => $id_skema,
                    'id_kuk' => $id_kuk,
                    'kompeten' => $item['kompeten'],
                    'keterangan' => $item['keterangan'],
                    'tanggal_observasi' => $tanggal_observasi
                ];
            }
        }

        // Perform batch insert if needed
        if (!empty($dataToInsert)) {
            $db->table($table)->insertBatch($dataToInsert);
        }

        return true;
    }

    /**
     * Helper method to save bulk KUK details (clear and re-insert all)
     */
    private function saveBulkDetailKUK($id_observasi, $id_skema, $kuk, $keterangan, $tanggal_observasi)
    {
        $table = 'detail_observasi';
        $db = $this->db;

        // Delete existing details
        $db->table($table)
            ->where('id_observasi', $id_observasi)
            ->where('id_skema', $id_skema)
            ->delete();

        // Prepare batch data for insertion
        $batch_data = [];
        foreach ($kuk as $id_kuk => $kompeten) {
            $batch_data[] = [
                'id_observasi' => $id_observasi,
                'id_skema' => $id_skema,
                'id_kuk' => $id_kuk,
                'kompeten' => $kompeten,
                'keterangan' => $keterangan[$id_kuk] ?? '',
                'tanggal_observasi' => $tanggal_observasi
            ];
        }

        if (!empty($batch_data)) {
            $db->table($table)->insertBatch($batch_data);
        }

        return true;
    }

    /**
     * Get observation data by ID
     */
    public function getObservasiById($id_observasi)
    {
        return $this->db->table($this->table)
            ->where('id_observasi', $id_observasi)
            ->get()
            ->getRowArray();
    }

    /**
     * Get observation details by observation ID and skema ID
     */
    public function getObservasiDetails($id_observasi, $id_skema)
    {
        return $this->db->table('detail_observasi')
            ->where('id_observasi', $id_observasi)
            ->where('id_skema', $id_skema)
            ->get()
            ->getResultArray();
    }

    /**
     * EAGER LOADING: Get observation data with all related information in single query - Updated for APL1
     * Eliminates N+1 problem when loading observation details
     *
     * @param int $id_observasi Observation ID
     * @return array|null Complete observation data with relationships
     */
    public function getObservasiWithAllDetails(int $id_observasi): ?array
    {
        $cacheKey = "observasi_details_{$id_observasi}";

        if ($cached = cache($cacheKey)) {
            return $cached;
        }

        $sql = "
        SELECT 
            -- Observasi data
            o.id_observasi,
            o.tanggal_observasi,
            o.id_asesor,
            o.id_apl1,
            
            -- Asesor data
            asesor.nomor_registrasi as asesor_nomor_registrasi,
            asesor_user.nama_lengkap as asesor_nama,
            asesor_user.email as asesor_email,
            
            -- Asesi data from APL1
            apl1.nik as asesi_nik,
            apl1.nama_siswa as asesi_nama,
            apl1.email as asesi_email,
            apl1.no_hp as asesi_phone,
            apl1.validasi_apl1 as status_validasi,
            
            -- Skema data
            s.id_skema,
            s.kode_skema,
            s.nama_skema,
            s.jenis_skema,
            
            -- Asesmen data
            asm.tujuan as tujuan_asesmen,
            asm.created_at as tanggal_asesmen,
            
            -- Aggregated statistics
            (SELECT COUNT(*) FROM detail_observasi do1 WHERE do1.id_observasi = o.id_observasi) as total_kuk,
            (SELECT COUNT(*) FROM detail_observasi do2 WHERE do2.id_observasi = o.id_observasi AND do2.kompeten = 'Ya') as kompeten_count,
            (SELECT COUNT(*) FROM detail_observasi do3 WHERE do3.id_observasi = o.id_observasi AND do3.kompeten = 'Tidak') as tidak_kompeten_count
            
        FROM observasi o
        INNER JOIN asesor ON asesor.id_asesor = o.id_asesor
        INNER JOIN users asesor_user ON asesor_user.id = asesor.id_user
        INNER JOIN apl1 ON apl1.id_apl1 = o.id_apl1
        INNER JOIN asesmen asm ON asm.id_asesmen = apl1.id_asesmen
        INNER JOIN skema s ON s.id_skema = asm.id_skema
        WHERE o.id_observasi = ?
        ";

        $query = $this->db->query($sql, [$id_observasi]);
        $result = $query->getRowArray();

        if (!$result) {
            return null;
        }

        // Get detailed KUK data in separate optimized query
        $detailSql = "
        SELECT 
            do.id_kuk,
            do.kompeten,
            do.keterangan,
            k.kode_kuk,
            k.pertanyaan,
            e.kode_elemen,
            e.nama_elemen,
            u.kode_unit,
            u.nama_unit
        FROM detail_observasi do
        INNER JOIN kuk k ON k.id_kuk = do.id_kuk
        INNER JOIN elemen e ON e.id_elemen = k.id_elemen
        INNER JOIN unit u ON u.id_unit = e.id_unit
        WHERE do.id_observasi = ?
        ORDER BY u.kode_unit, e.kode_elemen, k.kode_kuk
        ";

        $detailQuery = $this->db->query($detailSql, [$id_observasi]);
        $result['details'] = $detailQuery->getResultArray();

        // Calculate completion percentage
        $result['completion_percentage'] = $result['total_kuk'] > 0
            ? round(($result['kompeten_count'] + $result['tidak_kompeten_count']) / $result['total_kuk'] * 100, 1)
            : 0;

        // Cache for 30 minutes
        cache()->save($cacheKey, $result, 1800);

        return $result;
    }

    /**
     * EAGER LOADING: Get asesor with all competency schemas in single query
     * Eliminates N+1 when loading asesor competencies
     *
     * @param int $id_asesor Asesor ID
     * @return array|null Asesor data with all competency schemas
     */
    public function getAsesorWithAllSkema(int $id_asesor): ?array
    {
        $cacheKey = "asesor_skema_{$id_asesor}";

        if ($cached = cache($cacheKey)) {
            return $cached;
        }

        $sql = "
        SELECT 
            a.id_asesor,
            a.nomor_registrasi,
            u.nama_lengkap,
            u.email,
            u.active,
            
            -- Aggregate all skemas into JSON for easy handling
            JSON_ARRAYAGG(
                JSON_OBJECT(
                    'id_skema', s.id_skema,
                    'kode_skema', s.kode_skema,
                    'nama_skema', s.nama_skema,
                    'jenis_skema', s.jenis_skema,
                    'status', s.status
                )
            ) as skemas,
            
            -- Statistics
            COUNT(DISTINCT s.id_skema) as total_skemas,
            COUNT(DISTINCT CASE WHEN s.status = 'Y' THEN s.id_skema END) as active_skemas
            
        FROM asesor a
        INNER JOIN users u ON u.id = a.id_user
        LEFT JOIN asesor_skema ask ON ask.id_asesor = a.id_asesor
        LEFT JOIN skema s ON s.id_skema = ask.id_skema
        WHERE a.id_asesor = ?
        GROUP BY a.id_asesor, a.nomor_registrasi, u.nama_lengkap, u.email, u.active
        ";

        $query = $this->db->query($sql, [$id_asesor]);
        $result = $query->getRowArray();

        if (!$result) {
            return null;
        }

        // Decode JSON skemas
        $result['skemas'] = $result['skemas'] ? json_decode($result['skemas'], true) : [];

        // Cache for 1 hour
        cache()->save($cacheKey, $result, 3600);

        return $result;
    }

    /**
     * OPTIMIZED: Get DataTable data with eager loading - Updated for APL1
     * Single query with all joins to eliminate N+1 problem
     *
     * @param array $params DataTable parameters
     * @return array DataTable response
     */
    public function getOptimizedDataTableData(array $params): array
    {
        $draw = $params['draw'] ?? 1;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = $params['search']['value'] ?? '';

        // Build optimized query with all necessary joins for APL1
        $sql = "
        SELECT 
            o.id_observasi,
            o.tanggal_observasi,
            
            -- Asesor info
            asesor_user.nama_lengkap as nama_asesor,
            asesor.nomor_registrasi as reg_asesor,
            
            -- Asesi info from APL1
            apl1.nama_siswa as nama_asesi,
            apl1.nik as nik_asesi,
            apl1.email as email_asesi,
            
            -- Skema info
            s.kode_skema,
            s.nama_skema,
            
            -- Status info
            apl1.validasi_apl1 as status_pengajuan,
            
            -- Progress calculation
            COALESCE(progress.total_kuk, 0) as total_kuk,
            COALESCE(progress.filled_kuk, 0) as filled_kuk,
            CASE 
                WHEN COALESCE(progress.total_kuk, 0) = 0 THEN 0
                ELSE ROUND(COALESCE(progress.filled_kuk, 0) / progress.total_kuk * 100, 1)
            END as completion_percentage
            
        FROM observasi o
        INNER JOIN asesor ON asesor.id_asesor = o.id_asesor
        INNER JOIN users asesor_user ON asesor_user.id = asesor.id_user
        INNER JOIN apl1 ON apl1.id_apl1 = o.id_apl1  
        INNER JOIN asesmen asm ON asm.id_asesmen = apl1.id_asesmen
        INNER JOIN skema s ON s.id_skema = asm.id_skema
        LEFT JOIN (
            SELECT 
                do.id_observasi,
                COUNT(*) as total_kuk,
                SUM(CASE WHEN do.kompeten IS NOT NULL THEN 1 ELSE 0 END) as filled_kuk
            FROM detail_observasi do
            GROUP BY do.id_observasi
        ) progress ON progress.id_observasi = o.id_observasi
        ";

        // Add search conditions if search term provided
        $whereConditions = [];
        $searchParams = [];

        if (!empty($search)) {
            $whereConditions[] = "(
                asesor_user.nama_lengkap LIKE ? OR
                apl1.nama_siswa LIKE ? OR  
                apl1.nik LIKE ? OR
                s.kode_skema LIKE ? OR
                s.nama_skema LIKE ?
            )";
            $searchTerm = "%{$search}%";
            $searchParams = array_fill(0, 5, $searchTerm);
        }

        // Add WHERE clause if conditions exist
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        // Count total records for pagination
        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_query";
        $totalQuery = $this->db->query($countSql, $searchParams);
        $totalRecords = $totalQuery->getRowArray()['total'];

        // Add ORDER BY and LIMIT for pagination
        $sql .= " ORDER BY o.tanggal_observasi DESC LIMIT ? OFFSET ?";
        $searchParams[] = (int)$length;
        $searchParams[] = (int)$start;

        // Execute main query
        $query = $this->db->query($sql, $searchParams);
        $data = $query->getResultArray();

        return [
            'draw' => (int)$draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];
    }

    /**
     * BATCH LOADING: Get multiple observations with details in single query - Updated for APL1
     * Useful for reports and bulk operations
     *
     * @param array $observationIds Array of observation IDs
     * @return array Grouped observation data
     */
    public function getBatchObservationsWithDetails(array $observationIds): array
    {
        if (empty($observationIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($observationIds) - 1) . '?';

        $sql = "
        SELECT 
            o.id_observasi,
            o.tanggal_observasi,
            asesor_user.nama_lengkap as asesor_nama,
            apl1.nama_siswa as asesi_nama,
            s.kode_skema,
            s.nama_skema,
            
            -- Detail observasi
            do.id_kuk,
            do.kompeten,
            do.keterangan,
            k.kode_kuk,
          k.pertanyaan,
            e.kode_elemen,
            u.kode_unit
            
        FROM observasi o
        INNER JOIN asesor ON asesor.id_asesor = o.id_asesor
        INNER JOIN users asesor_user ON asesor_user.id = asesor.id_user
        INNER JOIN apl1 ON apl1.id_apl1 = o.id_apl1
        INNER JOIN asesmen asm ON asm.id_asesmen = apl1.id_asesmen
        INNER JOIN skema s ON s.id_skema = asm.id_skema
        LEFT JOIN detail_observasi do ON do.id_observasi = o.id_observasi
        LEFT JOIN kuk k ON k.id_kuk = do.id_kuk
        LEFT JOIN elemen e ON e.id_elemen = k.id_elemen
        LEFT JOIN unit u ON u.id_unit = e.id_unit
        WHERE o.id_observasi IN ({$placeholders})
        ORDER BY o.id_observasi, u.kode_unit, e.kode_elemen, k.kode_kuk
        ";

        $query = $this->db->query($sql, $observationIds);
        $rawData = $query->getResultArray();

        // Group data by observation ID
        $groupedData = [];
        foreach ($rawData as $row) {
            $observasiId = $row['id_observasi'];

            if (!isset($groupedData[$observasiId])) {
                $groupedData[$observasiId] = [
                    'id_observasi' => $observasiId,
                    'tanggal_observasi' => $row['tanggal_observasi'],
                    'asesor_nama' => $row['asesor_nama'],
                    'asesi_nama' => $row['asesi_nama'],
                    'kode_skema' => $row['kode_skema'],
                    'nama_skema' => $row['nama_skema'],
                    'details' => []
                ];
            }

            if ($row['id_kuk']) {
                $groupedData[$observasiId]['details'][] = [
                    'id_kuk' => $row['id_kuk'],
                    'kompeten' => $row['kompeten'],
                    'keterangan' => $row['keterangan'],
                    'kode_kuk' => $row['kode_kuk'],
                    'nama_kuk' => $row['nama_kuk'],
                    'kode_elemen' => $row['kode_elemen'],
                    'kode_unit' => $row['kode_unit']
                ];
            }
        }

        return array_values($groupedData);
    }

    /**
     * Get observation data for PDF generation - Updated for APL1
     */
    public function getObservasiForPDF(int $id_observasi): array
    {
        try {
            // Get main observasi data with correct relations for APL1
            $observasi = $this->db->table('observasi o')
                ->select([
                    'o.*',
                    'apl1.nik as nik_asesi',
                    'apl1.nama_siswa as nama_asesi',
                    'apl1.email as email_asesi',
                    'apl1.no_hp as phone_asesi',
                    'asesor_user.nama_lengkap as nama_asesor',
                    'asesor_user.email as email_asesor',
                    'skema.nama_skema',
                    'skema.kode_skema',
                    'skema.id_skema',
                    'asm.tujuan as tujuan_asesmen',
                    'tuk.nama_tuk',
                    'tuk.jenis_tuk'
                ])
                ->join('apl1', 'apl1.id_apl1 = o.id_apl1', 'inner')
                ->join('asesor', 'asesor.id_asesor = o.id_asesor', 'inner')
                ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen', 'inner')
                ->join('skema', 'skema.id_skema = asm.id_skema', 'inner')
                ->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left') // JOIN ke TUK
                ->where('o.id_observasi', $id_observasi)
                ->get()
                ->getRowArray();

            if (!$observasi) {
                return [
                    'success' => false,
                    'message' => 'Data observasi tidak ditemukan'
                ];
            }

            // Get detail observasi dengan struktur yang sesuai untuk VIEW yang sudah ada
            $detailObservasi = $this->getDetailObservasiForPDF($id_observasi, $observasi['id_skema']);

            return [
                'success' => true,
                'data' => [
                    'observasi' => $observasi,
                    'detailObservasi' => $detailObservasi,
                    'existing_data' => $this->getExistingById($id_observasi),
                    'skema' => [
                        'nama_skema' => $observasi['nama_skema'],
                        'kode_skema' => $observasi['kode_skema']
                    ]
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting observasi for PDF: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal mengambil data observasi: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get detail observasi dalam format yang sesuai dengan VIEW yang sudah ada
     */
    private function getDetailObservasiForPDF(int $id_observasi, int $id_skema): array
    {
        try {
            // Query untuk mendapatkan detail observasi yang sesuai dengan struktur VIEW
            $sql = "
                SELECT 
                    COALESCE(kk.id_kelompok, 1) as id_kelompok,
                    COALESCE(kk.nama_kelompok, 'Kelompok Utama') as nama_kelompok,
                    u.id_unit,
                    u.kode_unit,
                    u.nama_unit,
                    u.nama_unit as judul_unit,
                    e.id_elemen,
                    e.kode_elemen,
                    e.nama_elemen,
                    k.id_kuk,
                    k.kode_kuk,
                  k.pertanyaan as kriteria_unjuk_kerja
                FROM unit u
                LEFT JOIN kelompok_unit ku ON ku.id_unit = u.id_unit
                LEFT JOIN kelompok_kerja kk ON kk.id_kelompok = ku.id_kelompok AND kk.id_skema = u.id_skema
                INNER JOIN elemen e ON e.id_unit = u.id_unit
                INNER JOIN kuk k ON k.id_elemen = e.id_elemen
                WHERE u.id_skema = ? AND u.status = 'Y'
                ORDER BY 
                    COALESCE(kk.id_kelompok, 1),
                    u.kode_unit,
                    e.kode_elemen,
                    k.kode_kuk
            ";

            $rawData = $this->db->query($sql, [$id_skema])->getResultArray();

            // Format data sesuai dengan yang diharapkan oleh VIEW yang sudah ada
            $structured = [];

            foreach ($rawData as $row) {
                $kelompokId = $row['id_kelompok'];

                if (!isset($structured[$kelompokId])) {
                    $structured[$kelompokId] = [
                        'id_kelompok' => $kelompokId,
                        'nama_kelompok' => $row['nama_kelompok'],
                        'units' => []
                    ];
                }

                $unitId = $row['id_unit'];
                if (!isset($structured[$kelompokId]['units'][$unitId])) {
                    $structured[$kelompokId]['units'][$unitId] = [
                        'id_unit' => $unitId,
                        'kode_unit' => $row['kode_unit'],
                        'nama_unit' => $row['nama_unit'],
                        'judul_unit' => $row['judul_unit'], // Untuk kompatibilitas dengan VIEW
                        'kuk' => []
                    ];
                }

                // Add KUK data
                $structured[$kelompokId]['units'][$unitId]['kuk'][] = [
                    'id_kuk' => $row['id_kuk'],
                    'id_elemen' => $row['id_elemen'],
                    'kode_kuk' => $row['kode_kuk'],
                    'kriteria_unjuk_kerja' => $row['kriteria_unjuk_kerja'],
                    'nama_elemen' => $row['nama_elemen']
                ];
            }

            return $structured;
        } catch (\Exception $e) {
            log_message('error', 'Error getting detail observasi for PDF: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get APL1 data by ID - New method specific for APL1
     *
     * @param string $id_apl1 APL1 ID
     * @return array|null
     */
    public function getApl1Data(string $id_apl1): ?array
    {
        return $this->db->table('apl1')
            ->select([
                'apl1.*',
                'asesmen.id_skema',
                'asesmen.tujuan',
                'skema.nama_skema',
                'skema.kode_skema'
            ])
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner')
            ->where('apl1.id_apl1', $id_apl1)
            ->get()
            ->getRowArray();
    }

    /**
     * Get validated APL1 data for specific schema - New method for APL1
     *
     * @param int $id_skema Schema ID
     * @return array
     */
    public function getValidatedApl1BySkema(int $id_skema): array
    {
        return $this->db->table('apl1')
            ->select([
                'apl1.id_apl1',
                'apl1.nama_siswa',
                'apl1.nik',
                'apl1.email',
                'apl1.no_hp',
                'apl1.validasi_apl1'
            ])
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner')
            ->where('asesmen.id_skema', $id_skema)
            ->where('apl1.validasi_apl1', 'validated')
            ->get()
            ->getResultArray();
    }

    /**
     * Check if observation exists for APL1
     *
     * @param string $id_apl1 APL1 ID
     * @param int $id_asesor Asesor ID
     * @return array|null
     */
    public function checkExistingObservation(string $id_apl1, int $id_asesor): ?array
    {
        return $this->db->table('observasi')
            ->where('id_apl1', $id_apl1)
            ->where('id_asesor', $id_asesor)
            ->get()
            ->getRowArray();
    }
}
