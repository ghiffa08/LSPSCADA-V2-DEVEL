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
    protected $protectFields    = false;
    protected $allowedFields    = [
        'id_asesor',
        'id_asesi',
        'id_apl1',
        'id_asesmen',
        'id_skema',
        'id_kuk',
        'kompeten',
        'keterangan',
        'tanggal_observasi',
        'created_at',
        'updated_at'

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
    protected $dataTableSearchFields = ['observasi.id_asesor'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('asesi', 'asesi.id_asesi = observasi.id_asesi')
            ->join('users as asesi_user', 'asesi_user.id = asesi.user_id')
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_apl1 = observasi.id_apl1')
            ->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen')
            ->join('tuk', 'tuk.id_tuk = asesmen.id_tuk')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            ->join('users as asesor', 'asesor.id = observasi.id_asesor');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'observasi.*, 
            asesor.fullname AS nama_asesor, 
            asesi_user.fullname AS nama_asesi, 
            tuk.nama_tuk, 
            tuk.jenis_tuk, 
            skema.nama_skema'
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
     * Get asesi data for the given assessment ID
     */
    public function getAsesiBySkema($id_skema)
    {
        $builder = $this->db->table('asesmen');
        $builder->select('
            asesi.id_asesi, 
            users.fullname as nama_lengkap
        ');
        $builder->join('pengajuan_asesmen', 'pengajuan_asesmen.id_asesmen = asesmen.id_asesmen', 'inner');
        $builder->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi', 'inner');
        $builder->join('users', 'users.id = asesi.user_id', 'inner');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'inner');
        $builder->where('asesmen.id_skema', $id_skema);

        return $builder->get()->getResultArray();
    }

    /**
     * Get observation structure data for a specific schema
     *
     * @param int $id_skema Schema ID
     * @return array
     */
    public function getStrukturObservasiSkema(int $id_skema): array
    {
        $builder = $this->db->table('skema');

        $builder->select([
            'skema.id_skema',
            'skema.kode_skema',
            'skema.nama_skema',
            'skema.jenis_skema',
            'unit.id_unit',
            'unit.kode_unit',
            'unit.nama_unit',
            'elemen.id_elemen',
            'elemen.kode_elemen',
            'elemen.nama_elemen',
            'kuk.id_kuk',
            'kuk.kode_kuk',
            'kuk.nama_kuk AS kriteria_unjuk_kerja',
            'kelompok_kerja.id_kelompok',
            'kelompok_kerja.nama_kelompok'
        ]);

        // Optimize join order to start with smallest tables and filter early
        $builder->join('kelompok_kerja', 'kelompok_kerja.id_skema = skema.id_skema', 'inner');
        $builder->join('kelompok_unit', 'kelompok_unit.id_kelompok = kelompok_kerja.id_kelompok', 'inner');
        $builder->join('unit', 'unit.id_unit = kelompok_unit.id_unit AND unit.id_skema = skema.id_skema', 'inner');

        // Apply early filtering to reduce data before joining large tables
        $builder->where('skema.id_skema', $id_skema);
        $builder->where('skema.status', 'Y');
        $builder->where('unit.status', 'Y');

        // Now join larger tables after filtering
        $builder->join('elemen', 'elemen.id_unit = unit.id_unit AND elemen.id_skema = skema.id_skema', 'left');
        $builder->join('kuk', 'kuk.id_elemen = elemen.id_elemen AND kuk.id_unit = unit.id_unit AND kuk.id_skema = skema.id_skema', 'left');

        $builder->orderBy('unit.kode_unit', 'ASC');
        $builder->orderBy('elemen.kode_elemen', 'ASC');
        $builder->orderBy('kuk.kode_kuk', 'ASC');

        return $builder->get()->getResultArray();
    }


    /**
     * Get observation metadata including assessee signature only
     *
     * @param string $id_asesi Assessee ID
     * @param int $id_skema Schema ID
     * @return array|null
     */
    public function getObservasiData(string $id_asesi, int $id_skema): ?array
    {
        $builder = $this->db->table('observasi');

        $builder->select([
            'observasi.tanggal_observasi',
            'observasi.id_asesi',
            'observasi.id_asesor',
            'asesor.fullname AS nama_asesor',
            'asesor.tanda_tangan AS ttd_asesor',
            'asesi_user.fullname AS nama_asesi',
            'asesi_user.tanda_tangan AS ttd_asesi',
            'tuk.nama_tuk',
            'skema.nama_skema',
            'skema.kode_skema'
        ]);

        // Join tables
        $builder->join('asesi', 'asesi.id_asesi = observasi.id_asesi');
        $builder->join('users as asesi_user', 'asesi_user.id = asesi.user_id');
        $builder->join('pengajuan_asesmen', 'pengajuan_asesmen.id_apl1 = observasi.id_apl1');
        $builder->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen');
        $builder->join('tuk', 'tuk.id_tuk = asesmen.id_tuk');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema');
        $builder->join('users as asesor', 'asesor.id = observasi.id_asesor');

        // Apply filters
        $builder->where('observasi.id_asesi', $id_asesi);
        $builder->where('skema.id_skema', $id_skema);

        $result = $builder->get()->getRowArray();

        // Convert binary signature to base64 untuk kedua tanda tangan
        if ($result) {
            if (!empty($result['ttd_asesi'])) {
                $result['ttd_asesi_base64'] = 'data:image/png;base64,' . base64_encode($result['ttd_asesi']);
            }
            if (!empty($result['ttd_asesor'])) {
                $result['ttd_asesor_base64'] = 'data:image/png;base64,' . base64_encode($result['ttd_asesor']);
            }
        }

        return $result;
    }

    /**
     * Get existing observation data for a specific assessee
     *
     * @param int $id_asesi Assessee ID
     * @return array
     */
    public function getExistingObservasi(string $id_asesi): array
    {
        $builder = $this->db->table('detail_observasi');
        $builder->select('detail_observasi.id_kuk, detail_observasi.kompeten, detail_observasi.keterangan');
        $builder->join('observasi', 'observasi.id_observasi = detail_observasi.id_observasi', 'inner');
        $builder->where('observasi.id_asesi', $id_asesi);

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
     * Save observation data with details
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
                    ->where('id_asesi', $masterData['id_asesi'])
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
}
