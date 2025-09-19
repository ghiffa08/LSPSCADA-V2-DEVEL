<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class RekamanAsesmenModel extends Model
{
    use DataTableTrait;

    protected $table            = 'rekaman_asesmen';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_apl1',
        'id_asesor',
        'tanggal_rekaman',
        'rekomendasi',
        'tindak_lanjut',
        'komentar'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_asesor' => 'required|integer',
        'id_apl1' => 'required',
        'tanggal_rekaman' => 'required|valid_date',
        'rekomendasi' => 'in_list[kompeten,belum_kompeten]'
    ];

    protected $validationMessages = [
        'id_asesor' => [
            'required' => 'ID Asesor harus diisi',
            'integer' => 'ID Asesor harus berupa angka'
        ],
        'id_apl1' => [
            'required' => 'ID APL1 harus diisi'
        ],
        'tanggal_rekaman' => [
            'required' => 'Tanggal rekaman harus diisi',
            'valid_date' => 'Format tanggal tidak valid'
        ],
        'rekomendasi' => [
            'in_list' => 'Rekomendasi harus kompeten atau belum_kompeten'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = [
        'rekaman_asesmen.id_asesor',
        'apl1.nama_siswa',
        'apl1.nik',
        'apl1.email',
        'skema.nama_skema',
        'skema.kode_skema'
    ];

    /**
     * Apply joins for DataTable query
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder
            ->join('apl1', 'apl1.id_apl1 = rekaman_asesmen.id_apl1', 'inner')
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner')
            ->join('asesor', 'asesor.id_asesor = rekaman_asesmen.id_asesor', 'inner')
            ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');
    }

    /**
     * Apply custom select fields for DataTable query
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'rekaman_asesmen.*, 
            asesor_user.nama_lengkap AS nama_asesor, 
            apl1.nama_siswa AS nama_asesi,
            apl1.nik,
            apl1.email,
            skema.nama_skema,
            skema.kode_skema,
            apl1.validasi_apl1 as status_pengajuan'
        );
    }

    /**
     * Transform DataTable results if needed
     */
    protected function transformDataTableResults($data)
    {
        foreach ($data as &$row) {
            // Format tanggal rekaman
            if (isset($row['tanggal_rekaman'])) {
                $row['tanggal_rekaman_formatted'] = date('d/m/Y', strtotime($row['tanggal_rekaman']));
            }

            // Format rekomendasi
            if (isset($row['rekomendasi'])) {
                $row['rekomendasi_text'] = ucfirst(str_replace('_', ' ', $row['rekomendasi']));
            }
        }
        return $data;
    }

    /**
     * Get validated APL1 data by schema
     */
    public function getAsesiBySkema($id_skema)
    {
        $builder = $this->db->table('apl1');
        $builder->select('
            apl1.id_apl1, 
            apl1.nama_siswa as nama_lengkap,
            apl1.nik,
            apl1.email,
            apl1.validasi_apl1 as status_pengajuan
        ');
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->where('asesmen.id_skema', $id_skema);
        $builder->where('apl1.validasi_apl1', 'validated');
        $builder->orderBy('apl1.nama_siswa', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Helper untuk menyimpan detail kompetensi (clear and re-insert).
     * Diperbaiki untuk menangani nilai '0' dengan benar.
     */
    public function saveBulkKompetensiDetails(int $id_rekaman, array $kompetensi)
    {
        $db = $this->db;
        $detailTable = 'rekaman_asesmen_kompetensi';

        // 1. Hapus detail yang sudah ada untuk rekaman ini
        $db->table($detailTable)->where('id_rekaman', $id_rekaman)->delete();

        // 2. Siapkan data baru untuk batch insert
        $batchData = [];
        if (!empty($kompetensi)) {
            // Ambil semua unit ID dari skema untuk memastikan semua unit ada
            $rekamanData = $this->find($id_rekaman);
            $apl1Data = $this->db->table('apl1')->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen')->where('id_apl1', $rekamanData['id_apl1'])->get()->getRowArray();
            $allUnits = $this->db->table('unit')->where('id_skema', $apl1Data['id_skema'])->get()->getResultArray();

            foreach ($allUnits as $unit) {
                $id_unit = $unit['id_unit'];
                $data = $kompetensi[$id_unit] ?? [];

                // --- PERBAIKAN UTAMA DI SINI ---
                // Menggunakan nilai langsung dari $data, dengan fallback ke 0.
                $batchData[] = [
                    'id_rekaman'          => $id_rekaman,
                    'id_unit'             => $id_unit,
                    'metode_observasi'    => (int)($data['metode_observasi'] ?? 0),
                    'metode_portofolio'   => (int)($data['metode_portofolio'] ?? 0),
                    'metode_pihak_ketiga' => (int)($data['metode_pihak_ketiga'] ?? 0),
                    'metode_lisan'        => (int)($data['metode_lisan'] ?? 0),
                    'metode_tertulis'     => (int)($data['metode_tertulis'] ?? 0),
                    'metode_proyek'       => (int)($data['metode_proyek'] ?? 0),
                    'metode_lainnya'      => (int)($data['metode_lainnya'] ?? 0),
                ];
            }
        }

        // 3. Lakukan batch insert jika ada data baru
        if (!empty($batchData)) {
            $db->table($detailTable)->insertBatch($batchData);
        }

        return true;
    }

    /**
     * Get APL1 data by ID
     */
    public function getApl1Data($id_apl1)
    {
        $builder = $this->db->table('apl1');
        $builder->select([
            'apl1.*',
            'asesmen.id_asesmen',
            'asesmen.tujuan',
            'skema.id_skema',
            'skema.nama_skema',
            'skema.kode_skema'
        ]);
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');
        $builder->where('apl1.id_apl1', $id_apl1);

        return $builder->get()->getRowArray();
    }

    /**
     * Get rekaman data with complete information
     */
    public function getRekamanData($id_apl1, $id_asesor = null)
    {
        $builder = $this->db->table('rekaman_asesmen');
        $builder->select([
            'rekaman_asesmen.*',
            'apl1.nik',
            'apl1.nama_siswa as nama_asesi',
            'apl1.email as email_asesi',
            'apl1.tempat_lahir',
            'apl1.tanggal_lahir',
            'apl1.jenis_kelamin',
            'apl1.pendidikan_terakhir',
            'apl1.nama_sekolah',
            'apl1.jurusan',
            'apl1.pekerjaan',
            'apl1.nama_lembaga',
            'apl1.jabatan',
            'apl1.tanda_tangan_asesi',
            'asesor_user.nama_lengkap as nama_asesor',
            'asesor_user.email as email_asesor',
            'skema.nama_skema',
            'skema.kode_skema'
        ]);

        $builder->join('apl1', 'apl1.id_apl1 = rekaman_asesmen.id_apl1', 'inner');
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->join('asesor', 'asesor.id_asesor = rekaman_asesmen.id_asesor', 'inner');
        $builder->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');

        $builder->where('rekaman_asesmen.id_apl1', $id_apl1);

        if ($id_asesor) {
            $builder->where('rekaman_asesmen.id_asesor', $id_asesor);
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Get rekaman by ID with complete data
     */
    public function getById($id)
    {
        $builder = $this->db->table('rekaman_asesmen');
        $builder->select([
            'rekaman_asesmen.*',
            'apl1.nik as nik_asesi',
            'apl1.nama_siswa as nama_asesi',
            'apl1.email as email_asesi',
            'apl1.tempat_lahir',
            'apl1.tanggal_lahir',
            'apl1.jenis_kelamin',
            'apl1.pendidikan_terakhir',
            'apl1.nama_sekolah',
            'apl1.jurusan',
            'apl1.pekerjaan',
            'apl1.nama_lembaga',
            'apl1.jabatan',
            'apl1.tanda_tangan_asesi',
            'asesor_user.nama_lengkap as nama_asesor',
            'asesor_user.email as email_asesor',
            'skema.nama_skema',
            'skema.kode_skema',
            'skema.id_skema',
            'asesmen.tujuan as tujuan_asesmen',
            'tuk.nama_tuk',
            'tuk.jenis_tuk',
            'unit.nama_unit'
        ]);

        $builder->join('apl1', 'apl1.id_apl1 = rekaman_asesmen.id_apl1', 'inner');
        $builder->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'inner');
        $builder->join('asesor', 'asesor.id_asesor = rekaman_asesmen.id_asesor', 'inner');
        $builder->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');
        $builder->join('tuk', 'tuk.id_tuk = asesmen.id_tuk', 'left');

        $builder->where('rekaman_asesmen.id', $id);

        return $builder->get()->getRowArray();
    }

    /**
     * Get schema structure for rekaman
     */
    public function getStrukturRekamanSkema($id_skema)
    {
        $sql = "
        SELECT 
            s.id_skema,
            s.kode_skema,
            s.nama_skema,
            s.jenis_skema,
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
            k.pertanyaan AS kriteria_unjuk_kerja
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
        return $this->transformToHierarchicalStructure($rawData);
    }

    /**
     * Transform flat data to hierarchical structure
     */
    private function transformToHierarchicalStructure($rawData)
    {
        $structure = [
            'skema' => null,
            'kelompok_kerja' => []
        ];

        foreach ($rawData as $row) {
            if (!$structure['skema']) {
                $structure['skema'] = [
                    'id_skema' => $row['id_skema'],
                    'kode_skema' => $row['kode_skema'],
                    'nama_skema' => $row['nama_skema'],
                    'jenis_skema' => $row['jenis_skema']
                ];
            }

            $kelompokId = $row['id_kelompok'];
            $unitId = $row['id_unit'];
            $elemenId = $row['id_elemen'];

            if (!isset($structure['kelompok_kerja'][$kelompokId])) {
                $structure['kelompok_kerja'][$kelompokId] = [
                    'id_kelompok' => $kelompokId,
                    'nama_kelompok' => $row['nama_kelompok'],
                    'units' => []
                ];
            }

            if ($unitId && !isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId] = [
                    'id_unit' => $unitId,
                    'kode_unit' => $row['kode_unit'],
                    'nama_unit' => $row['nama_unit'],
                    'elemen' => []
                ];
            }

            if ($elemenId && $unitId && !isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId] = [
                    'id_elemen' => $elemenId,
                    'kode_elemen' => $row['kode_elemen'],
                    'nama_elemen' => $row['nama_elemen'],
                    'kuk' => []
                ];
            }

            if (!empty($row['id_kuk']) && $elemenId && $unitId) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][] = [
                    'id_kuk' => $row['id_kuk'],
                    'kode_kuk' => $row['kode_kuk'],
                    'kriteria_unjuk_kerja' => $row['kriteria_unjuk_kerja']
                ];
            }
        }

        // Convert to indexed arrays
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
     * Get units with their work groups
     */
    public function getWorkGroupsWithUnits($id_skema)
    {
        $sql = "
        SELECT 
            COALESCE(kk.id_kelompok, 1) as id_kelompok,
            COALESCE(kk.nama_kelompok, 'Kelompok Utama') as nama_kelompok,
            u.id_unit,
            u.kode_unit,
            u.nama_unit
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
                'nama_unit' => $row['nama_unit']
            ];
        }

        return array_values($groupedData);
    }

    /**
     * Get existing rekaman kompetensi data
     */
    public function getExistingRekaman($id_apl1)
    {
        $builder = $this->db->table('rekaman_asesmen_kompetensi');
        $builder->select('
            rekaman_asesmen_kompetensi.id_unit,
            rekaman_asesmen_kompetensi.metode_observasi,
            rekaman_asesmen_kompetensi.metode_portofolio,
            rekaman_asesmen_kompetensi.metode_pihak_ketiga,
            rekaman_asesmen_kompetensi.metode_lisan,
            rekaman_asesmen_kompetensi.metode_tertulis,
            rekaman_asesmen_kompetensi.metode_proyek,
            rekaman_asesmen_kompetensi.metode_lainnya
        ');
        $builder->join('rekaman_asesmen', 'rekaman_asesmen.id = rekaman_asesmen_kompetensi.id_rekaman', 'inner');
        $builder->where('rekaman_asesmen.id_apl1', $id_apl1);

        $result = $builder->get()->getResultArray();

        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['id_unit']] = [
                'metode_observasi' => (bool)$row['metode_observasi'],
                'metode_portofolio' => (bool)$row['metode_portofolio'],
                'metode_pihak_ketiga' => (bool)$row['metode_pihak_ketiga'],
                'metode_lisan' => (bool)$row['metode_lisan'],
                'metode_tertulis' => (bool)$row['metode_tertulis'],
                'metode_proyek' => (bool)$row['metode_proyek'],
                'metode_lainnya' => (bool)$row['metode_lainnya']
            ];
        }

        return $formatted;
    }

    /**
     * Get existing kompetensi data by rekaman ID
     */
    public function getExistingById($id_rekaman)
    {
        $builder = $this->db->table('rekaman_asesmen_kompetensi');
        $builder->select('
            id_unit,
            metode_observasi,
            metode_portofolio,
            metode_pihak_ketiga,
            metode_lisan,
            metode_tertulis,
            metode_proyek,
            metode_lainnya
        ');
        $builder->where('id_rekaman', $id_rekaman);

        $result = $builder->get()->getResultArray();

        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['id_unit']] = [
                'metode_observasi' => (bool)$row['metode_observasi'],
                'metode_portofolio' => (bool)$row['metode_portofolio'],
                'metode_pihak_ketiga' => (bool)$row['metode_pihak_ketiga'],
                'metode_lisan' => (bool)$row['metode_lisan'],
                'metode_tertulis' => (bool)$row['metode_tertulis'],
                'metode_proyek' => (bool)$row['metode_proyek'],
                'metode_lainnya' => (bool)$row['metode_lainnya']
            ];
        }

        return $formatted;
    }

    /**
     * Check if rekaman exists
     */
    public function checkExistingRekaman($id_apl1, $id_asesor)
    {
        $builder = $this->db->table($this->table);
        $builder->where('id_apl1', $id_apl1);
        $builder->where('id_asesor', $id_asesor);

        return $builder->get()->getRowArray();
    }

    /**
     * Menyimpan data rekaman (master & detail) dalam satu transaksi.
     * Versi ini sudah disederhanakan dan diperbaiki secara total.
     */
    public function saveRekamanData(array $masterData, ?array $detailData = null)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // Validasi data utama
            if (empty($masterData['id_apl1']) || empty($masterData['id_asesor'])) {
                throw new \Exception('ID APL1 dan ID Asesor wajib ada untuk menyimpan data.');
            }

            // Cek apakah record sudah ada atau perlu dibuat baru
            $existing = $this->where('id_apl1', $masterData['id_apl1'])
                ->where('id_asesor', $masterData['id_asesor'])
                ->first();

            if ($existing) {
                // Jika sudah ada, pastikan ID-nya digunakan untuk UPDATE
                $masterData['id'] = $existing['id'];
            }

            // Gunakan metode save() bawaan dari Model.
            // Ia akan otomatis INSERT jika 'id' tidak ada, dan UPDATE jika 'id' ada.
            if ($this->save($masterData) === false) {
                throw new \Exception('Gagal menyimpan data master rekaman: ' . implode(', ', $this->errors()));
            }

            // Dapatkan ID rekaman, baik yang baru dibuat atau yang sudah ada
            $id_rekaman = $existing['id'] ?? $this->getInsertID();

            // Proses data detail (kompetensi) jika ada
            if ($detailData && isset($detailData['kompetensi'])) {
                $this->saveBulkKompetensiDetails($id_rekaman, $detailData['kompetensi']);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaksi database gagal.');
            }

            return $id_rekaman; // Kembalikan ID rekaman yang diproses

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in saveRekamanData: ' . $e->getMessage());
            throw $e; // Lemparkan kembali error agar bisa ditangkap oleh Service/Controller
        }
    }

    /**
     * Save kompetensi data for auto-save functionality
     */
    private function saveKompetensiData($id_rekaman, $kompetensiData)
    {
        $table = 'rekaman_asesmen_kompetensi';

        foreach ($kompetensiData as $id_unit => $metodeData) {
            // Check if record exists
            $existing = $this->db->table($table)
                ->where('id_rekaman', $id_rekaman)
                ->where('id_unit', $id_unit)
                ->get()
                ->getRowArray();

            $unitData = [
                'metode_observasi' => isset($metodeData['metode_observasi']) ? (int)(bool)$metodeData['metode_observasi'] : 0,
                'metode_portofolio' => isset($metodeData['metode_portofolio']) ? (int)(bool)$metodeData['metode_portofolio'] : 0,
                'metode_pihak_ketiga' => isset($metodeData['metode_pihak_ketiga']) ? (int)(bool)$metodeData['metode_pihak_ketiga'] : 0,
                'metode_lisan' => isset($metodeData['metode_lisan']) ? (int)(bool)$metodeData['metode_lisan'] : 0,
                'metode_tertulis' => isset($metodeData['metode_tertulis']) ? (int)(bool)$metodeData['metode_tertulis'] : 0,
                'metode_proyek' => isset($metodeData['metode_proyek']) ? (int)(bool)$metodeData['metode_proyek'] : 0,
                'metode_lainnya' => isset($metodeData['metode_lainnya']) ? (int)(bool)$metodeData['metode_lainnya'] : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                // Update existing
                $this->db->table($table)
                    ->where('id_rekaman', $id_rekaman)
                    ->where('id_unit', $id_unit)
                    ->update($unitData);
            } else {
                // Insert new
                $unitData['id_rekaman'] = $id_rekaman;
                $unitData['id_unit'] = $id_unit;
                $unitData['created_at'] = date('Y-m-d H:i:s');

                $this->db->table($table)->insert($unitData);
            }
        }
    }

    /**
     * Save single unit method (for auto-save per checkbox)
     */
    public function saveUnitMethod($id_rekaman, $id_unit, $metode, $value)
    {
        $table = 'rekaman_asesmen_kompetensi';

        // Check if record exists
        $existing = $this->db->table($table)
            ->where('id_rekaman', $id_rekaman)
            ->where('id_unit', $id_unit)
            ->get()
            ->getRowArray();

        if ($existing) {
            // Update specific method
            $this->db->table($table)
                ->where('id_rekaman', $id_rekaman)
                ->where('id_unit', $id_unit)
                ->update([
                    $metode => (int)(bool)$value,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            // Create new record with default values
            $insertData = [
                'id_rekaman' => $id_rekaman,
                'id_unit' => $id_unit,
                'metode_observasi' => 0,
                'metode_portofolio' => 0,
                'metode_pihak_ketiga' => 0,
                'metode_lisan' => 0,
                'metode_tertulis' => 0,
                'metode_proyek' => 0,
                'metode_lainnya' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Set the specific method
            $insertData[$metode] = (int)(bool)$value;

            $this->db->table($table)->insert($insertData);
        }

        return true;
    }

    /**
     * Delete rekaman and related data
     */
    public function deleteRekaman($id_rekaman)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // Delete kompetensi data
            $db->table('rekaman_asesmen_kompetensi')
                ->where('id_rekaman', $id_rekaman)
                ->delete();

            // Delete main record
            $result = $this->delete($id_rekaman);

            $db->transComplete();

            return $db->transStatus() !== false;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error deleting rekaman: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengambil semua data yang diperlukan untuk generate PDF FR.AK.02.
     * Versi ini menghasilkan daftar unit kompetensi yang flat (tidak berkelompok).
     *
     * @param integer $id_rekaman
     * @return array
     */
    public function getRekamanForPDF(int $id_rekaman): array
    {
        try {
            // 1. Ambil data utama dari rekaman asesmen beserta relasinya
            $rekaman = $this->db->table('rekaman_asesmen r')
                ->select([
                    'r.id',
                    'r.tanggal_rekaman',
                    'r.rekomendasi',
                    'r.tindak_lanjut',
                    'r.komentar',
                    'apl1.nama_siswa as nama_asesi',
                    'asesor_user.nama_lengkap as nama_asesor',
                    'skema.nama_skema',
                    'skema.kode_skema',
                    'skema.id_skema',
                    'tuk.nama_tuk',
                    'tuk.jenis_tuk',
                    'set_tanggal.tanggal as tanggal_asesmen', // Mengambil tanggal dari set_tanggal
                    'apl1.tanda_tangan_asesi'
                ])
                ->join('apl1', 'apl1.id_apl1 = r.id_apl1', 'inner')
                ->join('asesor', 'asesor.id_asesor = r.id_asesor', 'inner')
                ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen', 'inner')
                ->join('skema', 'skema.id_skema = asm.id_skema', 'inner')
                ->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left')
                ->join('set_tanggal', 'set_tanggal.id_tanggal = asm.id_tanggal', 'left') // Join ke set_tanggal
                ->where('r.id', $id_rekaman)
                ->get()->getRowArray();

            if (!$rekaman) {
                return ['success' => false, 'message' => 'Data rekaman utama tidak ditemukan.'];
            }

            // 2. Ambil data detail kompetensi (checklist) sebagai daftar flat
            $kompetensiList = $this->db->table('unit u')
                ->select('u.id_unit, u.kode_unit, u.nama_unit, rak.*')
                ->join('rekaman_asesmen_kompetensi rak', 'rak.id_unit = u.id_unit AND rak.id_rekaman = ' . $this->db->escape($id_rekaman), 'left')
                ->where('u.id_skema', $rekaman['id_skema'])
                ->where('u.status', 'Y')
                ->orderBy('u.kode_unit', 'ASC')
                ->get()->getResultArray();

            return [
                'success' => true,
                'data' => [
                    'rekaman'    => $rekaman,
                    'kompetensi' => $kompetensiList
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', '[getRekamanForPDF] ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan saat mengambil data untuk PDF.'];
        }
    }

    /**
     * Get kompetensi details in format suitable for PDF generation
     */
    private function getKompetensiDetailsForPDF(int $id_rekaman, int $id_skema): array
    {
        try {
            // Query to get units with their kompetensi assessment methods
            $sql = "
                SELECT 
                    COALESCE(kk.id_kelompok, 1) as id_kelompok,
                    COALESCE(kk.nama_kelompok, 'Kelompok Utama') as nama_kelompok,
                    u.id_unit,
                    u.kode_unit,
                    u.nama_unit,
                    u.nama_unit as judul_unit,
                    rak.metode_observasi,
                    rak.metode_portofolio,
                    rak.metode_pihak_ketiga,
                    rak.metode_lisan,
                    rak.metode_tertulis,
                    rak.metode_proyek,
                    rak.metode_lainnya
                FROM unit u
                LEFT JOIN kelompok_unit ku ON ku.id_unit = u.id_unit
                LEFT JOIN kelompok_kerja kk ON kk.id_kelompok = ku.id_kelompok AND kk.id_skema = u.id_skema
                LEFT JOIN rekaman_asesmen_kompetensi rak ON rak.id_unit = u.id_unit AND rak.id_rekaman = ?
                WHERE u.id_skema = ? AND u.status = 'Y'
                ORDER BY 
                    COALESCE(kk.id_kelompok, 1),
                    u.kode_unit
            ";

            $rawData = $this->db->query($sql, [$id_rekaman, $id_skema])->getResultArray();

            // Format data as expected by PDF view
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

                // Add unit data with methods
                $structured[$kelompokId]['units'][] = [
                    'id_unit' => $row['id_unit'],
                    'kode_unit' => $row['kode_unit'],
                    'nama_unit' => $row['nama_unit'],
                    'judul_unit' => $row['judul_unit'],
                    'metode_observasi' => (bool)$row['metode_observasi'],
                    'metode_portofolio' => (bool)$row['metode_portofolio'],
                    'metode_pihak_ketiga' => (bool)$row['metode_pihak_ketiga'],
                    'metode_lisan' => (bool)$row['metode_lisan'],
                    'metode_tertulis' => (bool)$row['metode_tertulis'],
                    'metode_proyek' => (bool)$row['metode_proyek'],
                    'metode_lainnya' => (bool)$row['metode_lainnya']
                ];
            }

            return $structured;
        } catch (\Exception $e) {
            log_message('error', 'Error getting kompetensi details for PDF: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Mengambil daftar unit kompetensi (flat) berdasarkan ID Skema.
     */
    public function getUnitsBySkema(int $id_skema): array
    {
        return $this->db->table('unit')
            ->select('id_unit, kode_unit, nama_unit')
            ->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->orderBy('kode_unit', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Mengecek apakah rekaman sudah ada untuk APL1 dan Asesor tertentu.
     *
     * @param string $id_apl1
     * @param int    $id_asesor
     * @return array|null
     */
    // public function checkExistingRekaman(string $id_apl1, int $id_asesor): ?array
    // {
    //     return $this->where('id_apl1', $id_apl1)
    //         ->where('id_asesor', $id_asesor)
    //         ->first();
    // }
}
