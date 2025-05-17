<?php

namespace App\Models;

use CodeIgniter\Model;

class PengajuanAsesmenModel extends Model
{
    protected $table            = 'pengajuan_asesmen';
    protected $primaryKey       = 'id_apl1';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_apl1',
        'id_asesi',
        'id_asesmen',
        'status',
        'validator',
        'email_validasi',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    // Cache for improved performance
    protected $tempCache = [];

    /**
     * Get complete APL1 data by APL1 ID including all related information
     *
     * @param string $apl1Id
     * @param bool $useCache Whether to use internal request cache
     * @return array|null
     */
    public function getCompleteAPL1Data(string $apl1Id, bool $useCache = true)
    {
        // Check cache first if enabled
        $cacheKey = "apl1_complete_{$apl1Id}";
        if ($useCache && isset($this->tempCache[$cacheKey])) {
            return $this->tempCache[$cacheKey];
        }

        $db = \Config\Database::connect();

        // Use a Builder for more control and optimization
        $builder = $db->table('pengajuan_asesmen pa');

        // Select all necessary fields with table aliases to avoid ambiguity
        $builder->select('
            pa.*,
            a.id_asesi, a.user_id, a.nik, a.tempat_lahir, a.tanggal_lahir, 
            a.jenis_kelamin, a.pendidikan_terakhir, a.nama_sekolah, a.jurusan, 
            a.kebangsaan, a.telpon_rumah, a.pekerjaan, 
            a.nama_lembaga, a.jabatan, a.alamat_perusahaan, a.email_perusahaan, 
            a.no_telp_perusahaan, a.provinsi, a.kabupaten, a.kecamatan, a.kelurahan, 
            a.rt, a.rw, a.kode_pos, a.created_at as asesi_created_at, a.updated_at as asesi_updated_at,
            p.nama as nama_provinsi, k.nama as nama_kabupaten, kc.nama as nama_kecamatan, kl.nama as nama_desa,
            d.id_dokumen, d.pas_foto, d.bukti_pendidikan, d.file_ktp, d.raport, d.sertifikat_pkl,
            asm.id_asesmen, asm.tujuan,
            sk.id_skema, 
            sk.nama_skema, 
            sk.jenis_skema, 
            tuk.id_tuk,
            tuk.nama_tuk,
            st.id_tanggal,
            st.tanggal
        ');

        // Join all necessary tables
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->join('dokumen_apl1 d', 'd.id_apl1 = pa.id_apl1', 'left');
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');

        // Join related tables for asesmen details
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');

        // Join address tables with left joins (in case some address data is missing)
        $builder->join('wilayah_provinsi p', 'p.id = a.provinsi', 'left');
        $builder->join('wilayah_kabupaten k', 'k.id = a.kabupaten', 'left');
        $builder->join('wilayah_kecamatan kc', 'kc.id = a.kecamatan', 'left');
        $builder->join('wilayah_desa kl', 'kl.id  = a.kelurahan', 'left');

        // Filter by APL1 ID
        $builder->where('pa.id_apl1', $apl1Id);

        // Get the result as array
        $result = $builder->get()->getRowArray();

        // Process the result for better structure
        if ($result) {
            // Reorganize data into a more structured format
            $data = [
                'pengajuan' => [
                    'id_apl1' => $result['id_apl1'],
                    'id_asesi' => $result['id_asesi'],
                    'id_asesmen' => $result['id_asesmen'],
                    'status' => $result['status'],
                    'validator' => $result['validator'] ?? null,
                    'email_validasi' => $result['email_validasi'] ?? null,
                    'created_at' => $result['created_at'],
                    'updated_at' => $result['updated_at'],
                    'deleted_at' => $result['deleted_at'],
                ],
                'asesi' => [
                    'id_asesi' => $result['id_asesi'],
                    'user_id' => $result['user_id'],
                    'nik' => $result['nik'],
                    'nama' => $result['nama'],
                    'tempat_lahir' => $result['tempat_lahir'],
                    'tanggal_lahir' => $result['tanggal_lahir'],
                    'jenis_kelamin' => $result['jenis_kelamin'],
                    'pendidikan_terakhir' => $result['pendidikan_terakhir'],
                    'nama_sekolah' => $result['nama_sekolah'],
                    'jurusan' => $result['jurusan'],
                    'kebangsaan' => $result['kebangsaan'],
                    'telpon_rumah' => $result['telpon_rumah'],
                    'no_hp' => $result['no_hp'],
                    'email' => $result['email'],
                    'pekerjaan' => $result['pekerjaan'],
                    'nama_lembaga' => $result['nama_lembaga'],
                    'jabatan' => $result['jabatan'],
                    'alamat_perusahaan' => $result['alamat_perusahaan'],
                    'email_perusahaan' => $result['email_perusahaan'],
                    'no_telp_perusahaan' => $result['no_telp_perusahaan'],
                    'created_at' => $result['asesi_created_at'],
                    'updated_at' => $result['asesi_updated_at'],
                    'alamat' => [
                        'provinsi_id' => $result['provinsi'],
                        'kabupaten_id' => $result['kabupaten'],
                        'kecamatan_id' => $result['kecamatan'],
                        'kelurahan_id' => $result['kelurahan'],
                        'provinsi_nama' => $result['nama_provinsi'],
                        'kabupaten_nama' => $result['nama_kabupaten'],
                        'kecamatan_nama' => $result['nama_kecamatan'],
                        'desa_nama' => $result['nama_desa'],
                        'rt' => $result['rt'],
                        'rw' => $result['rw'],
                        'kode_pos' => $result['kode_pos'],
                    ]
                ],
                'dokumen' => $result['id_dokumen'] ? [
                    'id_dokumen' => $result['id_dokumen'],
                    'pas_foto' => $result['pas_foto'] ?? null,
                    'bukti_pendidikan' => $result['bukti_pendidikan'] ?? null,
                    'ktp' => $result['ktp'] ?? null,
                    'raport' => $result['raport'] ?? null,
                    'sertifikat_pkl' => $result['sertifikat_pkl'] ?? null,
                ] : null,
                'asesmen' => [
                    'id_asesmen' => $result['id_asesmen'],
                    'id_skema' => $result['id_skema'] ?? null,
                    'id_tuk' => $result['id_tuk'] ?? null,
                    'id_tanggal' => $result['id_tanggal'] ?? null,
                    'tujuan' => $result['tujuan'] ?? null,
                    'nama_skema' => $result['nama_skema'] ?? null,
                    'jenis_skema' => $result['jenis_skema'] ?? null,
                    'nama_tuk' => $result['nama_tuk'] ?? null,
                    'tanggal' => $result['tanggal'] ?? null
                ]
            ];

            // Store in cache if enabled
            if ($useCache) {
                $this->tempCache[$cacheKey] = $data;
            }

            return $data;
        }

        return null;
    }

    /**
     * Get list of APL1 data with pagination, filter and sorting
     *
     * @param array $filters Filter parameters
     * @param int $limit Items per page
     * @param int $offset Pagination offset
     * @param string $orderBy Field to order by
     * @param string $orderDir Order direction (asc or desc)
     * @return array
     */
    public function getAPL1List(
        array $filters = [],
        int $limit = 10,
        int $offset = 0,
        string $orderBy = 'pa.created_at',
        string $orderDir = 'desc'
    ) {
        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');

        // Select only necessary fields for list view
        $builder->select('
            pa.id_apl1, pa.id_asesi, pa.id_asesmen, pa.status, pa.created_at, 
            a.nama, a.nik, a.email, a.no_hp,
            sk.nama_skema, 
            tuk.nama_tuk,
            st.tanggal,
            asm.tujuan
        ');

        // Join tables
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');

        // Join related tables for skema and TUK details
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');

        $builder->where('pa.deleted_at', null);

        // Apply filters
        if (!empty($filters)) {
            if (isset($filters['status']) && $filters['status'] !== '') {
                $builder->where('pa.status', $filters['status']);
            }

            if (isset($filters['id_asesmen']) && $filters['id_asesmen'] !== '') {
                $builder->where('pa.id_asesmen', $filters['id_asesmen']);
            }

            if (isset($filters['id_asesi']) && $filters['id_asesi'] !== '') {
                $builder->where('pa.id_asesi', $filters['id_asesi']);
            }

            if (isset($filters['search']) && $filters['search'] !== '') {
                $builder->groupStart()
                    ->like('a.nama', $filters['search'])
                    ->orLike('a.nik', $filters['search'])
                    ->orLike('a.email', $filters['search'])
                    ->orLike('pa.id_apl1', $filters['search'])
                    ->orLike('sk.nama_skema', $filters['search'])
                    ->groupEnd();
            }

            if (isset($filters['date_start']) && $filters['date_start'] !== '') {
                $builder->where('pa.created_at >=', $filters['date_start']);
            }

            if (isset($filters['date_end']) && $filters['date_end'] !== '') {
                $builder->where('pa.created_at <=', $filters['date_end'] . ' 23:59:59');
            }
        }

        // Apply ordering
        $builder->orderBy($orderBy, $orderDir);

        // Count total results for pagination
        $totalResults = $builder->countAllResults(false);

        // Apply pagination
        $builder->limit($limit, $offset);

        // Get results
        $results = $builder->get()->getResultArray();

        return [
            'data' => $results,
            'total' => $totalResults,
            'limit' => $limit,
            'offset' => $offset,
            'pages' => ceil($totalResults / $limit)
        ];
    }

    /**
     * Get stats for dashboard
     *
     * @return array
     */
    public function getAPL1Stats()
    {
        $db = \Config\Database::connect();

        // Total APL1
        $totalCount = $this->countAllResults();

        // Count by validation status
        $statusCounts = $db->table('pengajuan_asesmen')
            ->select('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->getResultArray();

        // Format the status counts
        $statusStats = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0
        ];

        foreach ($statusCounts as $status) {
            if (isset($status['status']) && isset($statusStats[$status['status']])) {
                $statusStats[$status['status']] = (int)$status['count'];
            }
        }

        // Get popular assessments (top 5)
        $popularAssessments = $db->table('pengajuan_asesmen pa')
            ->select('sk.nama_skema, COUNT(*) as count')
            ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen')
            ->join('skema sk', 'sk.id_skema = asm.id_skema', 'left')
            ->groupBy('asm.id_skema')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return [
            'total' => $totalCount,
            'status' => $statusStats,
            'popular_assessments' => $popularAssessments
        ];
    }

    /**
     * Get APL1 by asesi ID with complete data
     *
     * @param string $asesiId
     * @return array
     */
    public function getCompleteAPL1ByAsesiId(string $asesiId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');

        // Select minimal fields for the listing
        $builder->select('
            pa.id_apl1, pa.id_asesmen, pa.status, pa.created_at,
            asm.tujuan,
            sk.nama_skema,
            tuk.nama_tuk,
            st.tanggal,
            d.pas_foto, d.bukti_pendidikan, d.ktp, d.raport, d.sertifikat_pkl
        ');

        // Join tables
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');
        $builder->join('dokumen_apl1 d', 'd.id_apl1 = pa.id_apl1', 'left');

        // Filter by asesi ID
        $builder->where('pa.id_asesi', $asesiId);

        // Order by latest first
        $builder->orderBy('pa.created_at', 'DESC');

        // Execute query
        $results = $builder->get()->getResultArray();

        // Calculate document completion percentage for each APL1
        foreach ($results as &$item) {
            $documentFields = ['pas_foto', 'bukti_pendidikan', 'ktp', 'raport', 'sertifikat_pkl'];
            $totalDocuments = count($documentFields);
            $completedDocuments = 0;

            foreach ($documentFields as $field) {
                if (!empty($item[$field])) {
                    $completedDocuments++;
                }
            }

            $item['document_completion'] = ($totalDocuments > 0)
                ? round(($completedDocuments / $totalDocuments) * 100)
                : 0;
        }

        return $results;
    }

    /**
     * Create a new APL1 with documents
     *
     * @param array $data
     * @param array $documents
     * @return bool|string APL1 ID on success, false on failure
     */
    public function createCompleteAPL1(array $data, array $documents = [])
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Insert pengajuan_asesmen record
            $result = $this->insert($data);

            if (!$result) {
                $db->transRollback();
                return false;
            }

            $apl1Id = $data['id_apl1'];

            // Insert documents if provided
            if (!empty($documents)) {
                $dokumenData = array_merge(['id_apl1' => $apl1Id], $documents);
                $dokumenModel = new DokumenApl1Model();

                if (!$dokumenModel->insert($dokumenData)) {
                    $db->transRollback();
                    return false;
                }
            }

            $db->transCommit();
            return $apl1Id;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error creating APL1: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing APL1 with documents
     *
     * @param string $apl1Id
     * @param array $data
     * @param array $documents
     * @return bool
     */
    public function updateCompleteAPL1(string $apl1Id, array $data, array $documents = [])
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Update pengajuan_asesmen record
            if (!empty($data)) {
                $result = $this->update($apl1Id, $data);

                if (!$result) {
                    $db->transRollback();
                    return false;
                }
            }

            // Update documents if provided
            if (!empty($documents)) {
                $dokumenModel = new DokumenApl1Model();
                $existingDokumen = $dokumenModel->where('id_apl1', $apl1Id)->first();

                if ($existingDokumen) {
                    if (!$dokumenModel->update($existingDokumen['id_dokumen'], $documents)) {
                        $db->transRollback();
                        return false;
                    }
                } else {
                    $dokumenData = array_merge(['id_apl1' => $apl1Id], $documents);
                    if (!$dokumenModel->insert($dokumenData)) {
                        $db->transRollback();
                        return false;
                    }
                }
            }

            $db->transCommit();

            // Clear cache for this APL1
            unset($this->tempCache["apl1_complete_{$apl1Id}"]);

            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error updating APL1: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete APL1 with all related data
     *
     * @param string $apl1Id
     * @return bool
     */
    public function deleteCompleteAPL1(string $apl1Id)
    {
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Delete documents first
            $dokumenModel = new DokumenApl1Model();
            $dokumenModel->where('id_apl1', $apl1Id)->delete();

            // Delete the APL1 record
            $this->delete($apl1Id);

            $db->transCommit();

            // Clear cache
            unset($this->tempCache["apl1_complete_{$apl1Id}"]);

            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error deleting APL1: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear the internal cache
     */
    public function clearCache()
    {
        $this->tempCache = [];
    }

    /**
     * Get pengajuan by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getPengajuanById(string $id)
    {
        return $this->find($id);
    }

    /**
     * Get all pengajuan by asesi ID
     *
     * @param string $asesiId
     * @return array
     */
    public function getPengajuanByAsesiId(string $asesiId)
    {
        return $this->where('id_asesi', $asesiId)->findAll();
    }

    /**
     * Get all pengajuan by asesmen ID
     *
     * @param int $asesmenId
     * @return array
     */
    public function getPengajuanByAsesmenId(int $asesmenId)
    {
        return $this->where('id_asesmen', $asesmenId)->findAll();
    }

    /**
     * Get pengajuan with asesi details
     *
     * @param string $id
     * @return array|null
     */
    public function getPengajuanWithAsesiDetails(string $id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');
        $builder->select('pa.*, a.nama, a.nik, a.email, a.no_hp, a.tempat_lahir, a.tanggal_lahir');
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->where('pa.id_apl1', $id);

        return $builder->get()->getRowArray();
    }

    /**
     * Get dokumen for a pengajuan
     *
     * @param string $id
     * @return array|null
     */
    public function getDokumen(string $id)
    {
        $dokumenModel = new DokumenApl1Model();
        return $dokumenModel->where('id_apl1', $id)->first();
    }

    /**
     * Get pengajuan with all related data
     *
     * @param string $id
     * @return array|null
     */
    public function getPengajuanComplete(string $id)
    {
        $data = $this->getPengajuanWithAsesiDetails($id);
        if ($data) {
            $data['dokumen'] = $this->getDokumen($id);

            // Get asesmen details
            $db = \Config\Database::connect();
            $builder = $db->table('asesmen');
            $builder->where('id_asesmen', $data['id_asesmen']);
            $data['asesmen'] = $builder->get()->getRowArray();
        }

        return $data;
    }

    /**
     * Update validation status
     *
     * @param string $id
     * @param string $status
     * @param int|null $adminId
     * @return bool
     */
    public function updateValidationStatus(string $id, string $status, int $adminId = null)
    {
        $data = [
            'status' => $status
        ];

        if ($adminId) {
            $data['validator'] = $adminId;
        }

        return $this->update($id, $data);
    }

    /**
     * Mark email as sent
     *
     * @param string $id
     * @return bool
     */
    public function markEmailSent(string $id)
    {
        return $this->update($id, ['email_validasi' => 1]);
    }

    /**
     * Get pending validations count
     *
     * @return int
     */
    public function getPendingValidationsCount()
    {
        return $this->where('status', 'pending')->countAllResults();
    }

    /**
     * Get list of all pengajuan with pagination and filters
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getPengajuanList(array $filters = [], int $limit = 10, int $offset = 0)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');
        $builder->select('pa.*, a.nama, a.nik, a.email, asm.nama_asesmen');
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');

        // Apply filters
        if (!empty($filters)) {
            if (isset($filters['status']) && $filters['status'] !== '') {
                $builder->where('pa.status', $filters['status']);
            }

            if (isset($filters['search']) && $filters['search'] !== '') {
                $builder->groupStart()
                    ->like('a.nama', $filters['search'])
                    ->orLike('a.nik', $filters['search'])
                    ->orLike('a.email', $filters['search'])
                    ->orLike('pa.id_apl1', $filters['search'])
                    ->groupEnd();
            }

            if (isset($filters['asesmen_id']) && $filters['asesmen_id'] !== '') {
                $builder->where('pa.id_asesmen', $filters['asesmen_id']);
            }
        }

        return [
            'data' => $builder->limit($limit, $offset)->get()->getResultArray(),
            'total' => $builder->countAllResults(false)
        ];
    }
}
