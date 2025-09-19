<?php

namespace App\Models;

use CodeIgniter\Model;

use App\Traits\DataTableTrait;
use Ramsey\Uuid\Uuid;

class PengajuanAsesmenModel extends Model
{
    use DataTableTrait;

    protected $table            = 'pengajuan_asesmen';
    protected $primaryKey       = 'id_pengajuan';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'id_pengajuan',
        'id_asesi',
        'id_asesor',
        'id_asesmen',
        'status_pengajuan',
        'status_asesmen',
        'validator_id',
        'validated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id_asesi' => 'required|integer',
        'id_asesmen' => 'required|integer',
        'status_pengajuan' => 'in_list[pending,diterima,ditolak,selesai]',
        'status_asesmen' => 'in_list[proses,kompeten,belum_kompeten]'
    ];

    protected $validationMessages   = [
        'id_asesi' => [
            'required' => 'ID Asesi harus diisi',
            'integer' => 'ID Asesi harus berupa angka'
        ],
        'id_asesmen' => [
            'required' => 'ID Asesmen harus diisi',
            'integer' => 'ID Asesmen harus berupa angka'
        ],
        'status_pengajuan' => [
            'in_list' => 'Status pengajuan tidak valid'
        ],
        'status_asesmen' => [
            'in_list' => 'Status asesmen tidak valid'
        ]
    ];

    protected $skipValidation       = false;

    // Callbacks
    protected $beforeInsert = ['generateUUID'];

    /**
     * [UBAH] Method callback untuk men-generate UUID sebelum data disimpan.
     */
    protected function generateUUID(array $data)
    {
        $data['data']['id_pengajuan'] = Uuid::uuid4()->toString();
        return $data;
    }

    // Cache for improved performance
    protected $tempCache = [];

    protected array $dataTableSearchFields = [
        'u.nama_lengkap',
        'sk.nama_skema',
        'u_asesor.nama_lengkap'
    ];

    protected function applyDataTableSelects(object $builder): object
    {
        return $builder->select('
            pengajuan_asesmen.id_pengajuan,
            pengajuan_asesmen.status_pengajuan,
            pengajuan_asesmen.status_asesmen,
            pengajuan_asesmen.created_at,
            u.nama_lengkap as nama_asesi,
            sk.nama_skema,
            u_asesor.nama_lengkap as nama_asesor
        ');
    }

    protected function applyDataTableJoins(object $builder): object
    {
        $builder
            ->join('asesi a', 'a.id_asesi = pengajuan_asesmen.id_asesi', 'left')
            ->join('users u', 'u.id = a.id_user', 'left')
            ->join('asesmen asm', 'asm.id_asesmen = pengajuan_asesmen.id_asesmen', 'left')
            ->join('skema sk', 'sk.id_skema = asm.id_skema', 'left')
            ->join('asesor asr', 'asr.id_asesor = pengajuan_asesmen.id_asesor', 'left')
            ->join('users u_asesor', 'u_asesor.id = asr.id_user', 'left');

        return $builder;
    }

    public function getPengajuanById($id)
    {
        return $this->db->table('pengajuan_asesmen pa')
            ->select('
                pa.*,
                asm.id_skema, asm.tujuan,
                sk.nama_skema, sk.jenis_skema,
                a.id_asesi, a.kode_asesi, a.nik, a.tempat_lahir, a.tanggal_lahir, a.jenis_kelamin,
                a.pendidikan_terakhir, a.nama_sekolah, a.jurusan, a.kebangsaan, a.telpon_rumah, a.pekerjaan,
                a.nama_lembaga, a.jabatan, a.alamat_perusahaan, a.email_perusahaan, a.no_telp_perusahaan,
                a.provinsi, a.kabupaten, a.kecamatan, a.kelurahan, a.rt, a.rw, a.kode_pos,
                u.nama_lengkap, u.email, u.no_hp, u.username
            ')
            ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'left')
            ->join('skema sk', 'sk.id_skema = asm.id_skema', 'left')
            ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'left')
            ->join('users u', 'u.id = a.id_user', 'left')
            ->where('pa.id_pengajuan', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Get complete pengajuan data by ID including all related information
     *
     * @param int $pengajuanId
     * @param bool $useCache Whether to use internal request cache
     * @return array|null
     */
    public function getCompletePengajuanData(string $pengajuanId, bool $useCache = true)
    {
        // Check cache first if enabled
        $cacheKey = "pengajuan_complete_{$pengajuanId}";
        if ($useCache && isset($this->tempCache[$cacheKey])) {
            return $this->tempCache[$cacheKey];
        }

        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');

        // Select all necessary fields with table aliases to avoid ambiguity
        $builder->select('
        pa.*,
        a.id_asesi, a.id_user, a.kode_asesi, a.nik, a.tempat_lahir, a.tanggal_lahir, 
        a.jenis_kelamin, a.pendidikan_terakhir, a.nama_sekolah, a.jurusan, 
        a.kebangsaan, a.telpon_rumah, a.pekerjaan, 
        a.nama_lembaga, a.jabatan, a.alamat_perusahaan, a.email_perusahaan, 
        a.no_telp_perusahaan, a.provinsi, a.kabupaten, a.kecamatan, a.kelurahan, 
        a.rt, a.rw, a.kode_pos, a.pas_foto, a.bukti_pendidikan, a.ktp, 
        a.tanda_tangan_asesi, a.raport, a.sertifikat_pkl,
        a.created_at as asesi_created_at, a.updated_at as asesi_updated_at,
        u.nama_lengkap, u.email, u.no_hp, u.username,
        p.nama as nama_provinsi, k.nama as nama_kabupaten, kc.nama as nama_kecamatan, kl.nama as nama_desa,
        asm.id_asesmen, asm.tujuan,
        sk.id_skema, sk.nama_skema, sk.jenis_skema, 
        tuk.id_tuk, tuk.nama_tuk,
        st.id_tanggal, st.tanggal,
        asr.id_asesor, asr.nomor_registrasi as no_reg,
        u_asesor.nama_lengkap as nama_asesor
    ');

        // Join all necessary tables with corrected order
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->join('users u', 'u.id = a.id_user', 'left');
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');
        $builder->join('asesor asr', 'asr.id_asesor = pa.id_asesor', 'left'); // Define 'asr' first
        $builder->join('users u_asesor', 'u_asesor.id = asr.id_user', 'left'); // Then use 'asr'

        // Join related tables for asesmen details
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');

        // Join address tables
        $builder->join('wilayah_provinsi p', 'p.id = a.provinsi', 'left');
        $builder->join('wilayah_kabupaten k', 'k.id = a.kabupaten', 'left');
        $builder->join('wilayah_kecamatan kc', 'kc.id = a.kecamatan', 'left');
        $builder->join('wilayah_desa kl', 'kl.id = a.kelurahan', 'left');

        // Filter by pengajuan ID
        $builder->where('pa.id_pengajuan', $pengajuanId);
        $builder->where('pa.deleted_at', null);

        // Get the result as array
        $result = $builder->get()->getRowArray();

        // Process the result for better structure (no changes needed here)
        if ($result) {
            // ... (seluruh blok `if ($result)` dan isinya tetap sama)
            $data = [
                'pengajuan' => [
                    'id_pengajuan' => $result['id_pengajuan'],
                    'id_asesi' => $result['id_asesi'],
                    'id_asesor' => $result['id_asesor'],
                    'id_asesmen' => $result['id_asesmen'],
                    'status_pengajuan' => $result['status_pengajuan'],
                    'status_asesmen' => $result['status_asesmen'],
                    'tanggal_pengajuan' => $result['tanggal_pengajuan'],
                    'created_at' => $result['created_at'],
                    'updated_at' => $result['updated_at'],
                    'deleted_at' => $result['deleted_at'],
                ],
                'asesi' => [
                    'id_asesi' => $result['id_asesi'],
                    'id_user' => $result['id_user'],
                    'kode_asesi' => $result['kode_asesi'],
                    'nik' => $result['nik'],
                    'nama_lengkap' => $result['nama_lengkap'],
                    'username' => $result['username'],
                    'email' => $result['email'],
                    'no_hp' => $result['no_hp'],
                    'tempat_lahir' => $result['tempat_lahir'],
                    'tanggal_lahir' => $result['tanggal_lahir'],
                    'jenis_kelamin' => $result['jenis_kelamin'],
                    'pendidikan_terakhir' => $result['pendidikan_terakhir'],
                    'nama_sekolah' => $result['nama_sekolah'],
                    'jurusan' => $result['jurusan'],
                    'kebangsaan' => $result['kebangsaan'],
                    'telpon_rumah' => $result['telpon_rumah'],
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
                'dokumen' => [
                    'pas_foto' => $result['pas_foto'] ?? null,
                    'bukti_pendidikan' => $result['bukti_pendidikan'] ?? null,
                    'ktp' => $result['ktp'] ?? null,
                    'tanda_tangan_asesi' => $result['tanda_tangan_asesi'] ?? null,
                    'raport' => $result['raport'] ?? null,
                    'sertifikat_pkl' => $result['sertifikat_pkl'] ?? null,
                ],
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
                ],
                'asesor' => $result['id_asesor'] ? [
                    'id_asesor' => $result['id_asesor'],
                    'nama_asesor' => $result['nama_asesor'],
                    'no_reg' => $result['no_reg']
                ] : null
            ];

            if ($useCache) {
                $this->tempCache[$cacheKey] = $data;
            }

            return $data;
        }

        return null;
    }

    /**
     * Get pengajuan data by user ID
     *
     * @param int $userId User ID from users table
     * @param array $filters Additional filters (optional)
     * @param int $limit Items per page (default: 10, 0 for all)
     * @param int $offset Pagination offset
     * @param string $orderBy Field to order by
     * @param string $orderDir Order direction (asc or desc)
     * @return array
     */
    public function getPengajuanByUserId(
        int $userId,
        array $filters = [],
        int $limit = 10,
        int $offset = 0,
        string $orderBy = 'pa.created_at',
        string $orderDir = 'desc'
    ): array {
        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');

        // Select necessary fields
        $builder->select('
            pa.id_pengajuan, 
            pa.id_asesi, 
            pa.id_asesmen, 
            pa.id_asesor,
            pa.status_pengajuan, 
            pa.status_asesmen, 
            pa.created_at, 
            pa.updated_at,
            a.kode_asesi,
            a.nik,
            u.nama_lengkap,
            u.email,
            u.username,
            asm.tujuan,
            sk.id_skema,
            sk.nama_skema, 
            sk.jenis_skema,
            tuk.nama_tuk,
            st.tanggal,
            u_asesor.nama_lengkap as nama_asesor,
            asr.nomor_registrasi as no_reg_asesor
        ');

        // Join tables to get user data through asesi relationship
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');
        $builder->join('asesor asr', 'asr.id_asesor = pa.id_asesor', 'left');
        $builder->join('users u_asesor', 'u_asesor.id = asr.id_user', 'left');

        // Filter by user ID and non-deleted records
        $builder->where('u.id', $userId);
        $builder->where('pa.deleted_at', null);

        // Apply additional filters if provided
        if (!empty($filters)) {
            if (isset($filters['status_pengajuan']) && $filters['status_pengajuan'] !== '') {
                $builder->where('pa.status_pengajuan', $filters['status_pengajuan']);
            }

            if (isset($filters['status_asesmen']) && $filters['status_asesmen'] !== '') {
                $builder->where('pa.status_asesmen', $filters['status_asesmen']);
            }

            if (isset($filters['id_asesmen']) && $filters['id_asesmen'] !== '') {
                $builder->where('pa.id_asesmen', $filters['id_asesmen']);
            }

            if (isset($filters['id_skema']) && $filters['id_skema'] !== '') {
                $builder->where('sk.id_skema', $filters['id_skema']);
            }

            if (isset($filters['search']) && $filters['search'] !== '') {
                $builder->groupStart()
                    ->like('sk.nama_skema', $filters['search'])
                    ->orLike('a.kode_asesi', $filters['search'])
                    ->orLike('asm.tujuan', $filters['search'])
                    ->orLike('tuk.nama_tuk', $filters['search'])
                    ->groupEnd();
            }

            if (isset($filters['date_start']) && $filters['date_start'] !== '') {
                $builder->where('pa.created_at >=', $filters['date_start']);
            }

            if (isset($filters['date_end']) && $filters['date_end'] !== '') {
                $builder->where('pa.created_at <=', $filters['date_end'] . ' 23:59:59');
            }

            if (isset($filters['tanggal_asesmen']) && $filters['tanggal_asesmen'] !== '') {
                $builder->where('st.tanggal', $filters['tanggal_asesmen']);
            }
        }

        // Apply ordering
        $builder->orderBy($orderBy, $orderDir);

        // Count total results for pagination
        $totalResults = $builder->countAllResults(false);

        // Apply pagination if limit > 0
        if ($limit > 0) {
            $builder->limit($limit, $offset);
        }

        // Get results
        $results = $builder->get()->getResultArray();

        return [
            'data' => $results,
            'total' => $totalResults,
            'limit' => $limit,
            'offset' => $offset,
            'pages' => $limit > 0 ? ceil($totalResults / $limit) : 1
        ];
    }

    /**
     * Get list of pengajuan data with pagination, filter and sorting
     *
     * @param array $filters Filter parameters
     * @param int $limit Items per page
     * @param int $offset Pagination offset
     * @param string $orderBy Field to order by
     * @param string $orderDir Order direction (asc or desc)
     * @return array
     */
    public function getPengajuanList(
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
        pa.id_pengajuan, pa.id_asesi, pa.id_asesmen, pa.id_asesor, 
        pa.status_pengajuan, pa.status_asesmen, pa.created_at, 
        u.nama_lengkap, a.kode_asesi,
        sk.nama_skema, 
        tuk.nama_tuk,
        st.tanggal,
        asm.tujuan,
        u_asesor.nama_lengkap as nama_asesor
    ');

        // Join tables with corrected approach
        $builder->join('asesi a', 'a.id_asesi = pa.id_asesi');
        $builder->join('users u', 'u.id = a.id_user', 'left');
        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');
        $builder->join('asesor asr', 'asr.id_asesor = pa.id_asesor', 'left');
        $builder->join('users u_asesor', 'u_asesor.id = asr.id_user', 'left'); // Join to get asesor's name

        // Join related tables for skema and TUK details
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');

        $builder->where('pa.deleted_at', null);

        // ... (blok `if (!empty($filters))` dan sisa fungsi tetap sama)
        if (!empty($filters)) {
            if (isset($filters['status_pengajuan']) && $filters['status_pengajuan'] !== '') {
                $builder->where('pa.status_pengajuan', $filters['status_pengajuan']);
            }

            if (isset($filters['status_asesmen']) && $filters['status_asesmen'] !== '') {
                $builder->where('pa.status_asesmen', $filters['status_asesmen']);
            }

            if (isset($filters['id_asesmen']) && $filters['id_asesmen'] !== '') {
                $builder->where('pa.id_asesmen', $filters['id_asesmen']);
            }

            if (isset($filters['id_asesi']) && $filters['id_asesi'] !== '') {
                $builder->where('pa.id_asesi', $filters['id_asesi']);
            }

            if (isset($filters['id_asesor']) && $filters['id_asesor'] !== '') {
                $builder->where('pa.id_asesor', $filters['id_asesor']);
            }

            if (isset($filters['search']) && $filters['search'] !== '') {
                $builder->groupStart()
                    ->like('u.nama_lengkap', $filters['search'])
                    ->orLike('u.username', $filters['search'])
                    ->orLike('a.nik', $filters['search'])
                    ->orLike('u.email', $filters['search'])
                    ->orLike('a.kode_asesi', $filters['search'])
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
    public function getPengajuanStats()
    {
        $db = \Config\Database::connect();

        // Total pengajuan
        $totalCount = $this->where('deleted_at', null)->countAllResults();

        // Count by status pengajuan
        $statusPengajuanCounts = $db->table('pengajuan_asesmen')
            ->select('status_pengajuan, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('status_pengajuan')
            ->get()
            ->getResultArray();

        // Count by status asesmen
        $statusAsesmenCounts = $db->table('pengajuan_asesmen')
            ->select('status_asesmen, COUNT(*) as count')
            ->where('deleted_at', null)
            ->groupBy('status_asesmen')
            ->get()
            ->getResultArray();

        // Format the status counts
        $statusPengajuanStats = [
            'pending' => 0,
            'diterima' => 0,
            'ditolak' => 0,
            'selesai' => 0
        ];

        $statusAsesmenStats = [
            'proses' => 0,
            'kompeten' => 0,
            'belum_kompeten' => 0
        ];

        foreach ($statusPengajuanCounts as $status) {
            if (isset($status['status_pengajuan']) && isset($statusPengajuanStats[$status['status_pengajuan']])) {
                $statusPengajuanStats[$status['status_pengajuan']] = (int)$status['count'];
            }
        }

        foreach ($statusAsesmenCounts as $status) {
            if (isset($status['status_asesmen']) && isset($statusAsesmenStats[$status['status_asesmen']])) {
                $statusAsesmenStats[$status['status_asesmen']] = (int)$status['count'];
            }
        }

        // Get popular assessments (top 5)
        $popularAssessments = $db->table('pengajuan_asesmen pa')
            ->select('sk.nama_skema, COUNT(*) as count')
            ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen')
            ->join('skema sk', 'sk.id_skema = asm.id_skema', 'left')
            ->where('pa.deleted_at', null)
            ->groupBy('asm.id_skema')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();

        return [
            'total' => $totalCount,
            'status_pengajuan' => $statusPengajuanStats,
            'status_asesmen' => $statusAsesmenStats,
            'popular_assessments' => $popularAssessments
        ];
    }

    /**
     * Get pengajuan by asesi ID
     *
     * @param int $asesiId
     * @return array
     */
    public function getPengajuanByAsesiId(int $asesiId)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('pengajuan_asesmen pa');

        $builder->select('
        pa.*, 
        asm.tujuan,
        sk.nama_skema,
        tuk.nama_tuk,
        st.tanggal,
        u_asesor.nama_lengkap as nama_asesor
    ');

        $builder->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen');
        $builder->join('skema sk', 'sk.id_skema = asm.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left');
        $builder->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal', 'left');
        $builder->join('asesor asr', 'asr.id_asesor = pa.id_asesor', 'left');
        $builder->join('users u_asesor', 'u_asesor.id = asr.id_user', 'left'); // Join to get asesor's name

        $builder->where('pa.id_asesi', $asesiId);
        $builder->where('pa.deleted_at', null);
        $builder->orderBy('pa.created_at', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * [UBAH] Update status pengajuan. Type hint diubah dari int ke string.
     *
     * @param string $id
     * @param string $status
     * @param int|null $asesorId
     * @return bool
     */
    public function updateStatusPengajuan(string $id, string $status, int $asesorId = null)
    {
        $data = [
            'status_pengajuan' => $status
        ];

        if ($asesorId && $status === 'diterima') {
            $data['id_asesor'] = $asesorId;
        }

        return $this->update($id, $data);
    }

    /**
     * Update status asesmen
     *
     * @param string $id
     * @param string $status
     * @return bool
     */
    public function updateStatusAsesmen(string $id, string $status)
    {
        return $this->update($id, ['status_asesmen' => $status]);
    }

    /**
     * Get asesi data by asesmen ID for observation checklist
     *
     * @param string $id_asesmen
     * @return array
     */
    public function getAsesiByAsesmen(string $id_asesmen): array
    {
        try {
            $result = $this->db->table('pengajuan_asesmen pa')
                ->select('
                    pa.id_pengajuan,
                    pa.id_asesi,
                    pa.id_asesmen,
                    pa.status_pengajuan,
                    pa.status_asesmen,
                    a.nik,
                    a.kode_asesi,
                    u.nama_lengkap as nama,
                    u.nama_lengkap as nama_asesi,
                    u.nama_lengkap as nama_lengkap,
                    u.username,
                    u.email,
                    s.nama_skema,
                    s.kode_skema
                ')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'inner')
                ->join('users u', 'u.id = a.id_user', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'left')
                ->join('skema s', 's.id_skema = asm.id_skema', 'left')
                ->where('pa.id_asesmen', $id_asesmen)
                ->where('pa.status_pengajuan', 'diterima')
                ->where('pa.deleted_at', null)
                ->orderBy('u.nama_lengkap', 'ASC')
                ->get()
                ->getResultArray();

            log_message('info', 'Found ' . count($result) . ' asesi for asesmen ' . $id_asesmen);

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error in getAsesiByAsesmen: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new pengajuan
     *
     * @param array $data
     * @return bool|int
     */
    public function createPengajuan(array $data)
    {
        // Validasi data sebelum insert
        if (!$this->validate($data)) {
            log_message('error', 'Validation failed: ' . json_encode($this->errors()));
            return false;
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            // Insert data
            $result = $this->insert($data);

            if (!$result) {
                $db->transRollback();
                log_message('error', 'Insert failed: ' . json_encode($this->errors()));
                return false;
            }

            $db->transCommit();
            log_message('info', 'Pengajuan created successfully with ID: ' . $this->getInsertID());
            return $this->getInsertID();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error creating pengajuan: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get pending pengajuan count
     *
     * @return int
     */
    public function getPendingCount()
    {
        return $this->where('status_pengajuan', 'pending')
            ->where('deleted_at', null)
            ->countAllResults();
    }

    /**
     * Get pengajuan by asesmen ID
     *
     * @param int $asesmenId
     * @return array
     */
    public function getPengajuanByAsesmenId(int $asesmenId)
    {
        return $this->where('id_asesmen', $asesmenId)
            ->where('deleted_at', null)
            ->findAll();
    }

    /**
     * Get validator (admin) data by user ID.
     *
     * @param int $userId
     * @return array|null
     */
    public function getValidatorData(int $userId): ?array
    {
        return $this->db->table('users u')
            ->select('u.nama_lengkap, a.tanda_tangan_admin')
            ->join('admin a', 'a.id_user = u.id', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();
    }

    /**
     * Clear the internal cache
     */
    public function clearCache()
    {
        $this->tempCache = [];
    }
}
