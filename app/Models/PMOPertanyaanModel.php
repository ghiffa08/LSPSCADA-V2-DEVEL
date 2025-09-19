<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Traits\DataTableTrait;

class PMOPertanyaanModel extends Model
{
    use DataTableTrait;

    protected $table = 'pmo_pertanyaan';
    protected $primaryKey = 'id_pertanyaan';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_skema',
        'id_unit',
        'id_elemen',
        'id_kuk',
        'pertanyaan',
        'jenis_jawaban',
        'urutan',
        'aktif'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField; // Tidak ada soft delete

    // Validation
    protected $validationRules = [
        'id_skema' => 'required|integer',
        'id_unit' => 'required|integer',
        'id_elemen' => 'required|integer',
        'id_kuk' => 'required|integer',
        'pertanyaan' => 'required',
        'jenis_jawaban' => 'required|in_list[YA_TIDAK,PILIHAN_GANDA,ESSAY]',
        'urutan' => 'permit_empty|integer',
        'aktif' => 'in_list[Y,N]'
    ];

    protected $validationMessages = [
        'id_skema' => [
            'required' => 'ID Skema wajib diisi.',
            'integer' => 'ID Skema harus berupa angka.'
        ],
        'id_unit' => [
            'required' => 'ID Unit wajib diisi.',
            'integer' => 'ID Unit harus berupa angka.'
        ],
        'id_elemen' => [
            'required' => 'ID Elemen wajib diisi.',
            'integer' => 'ID Elemen harus berupa angka.'
        ],
        'id_kuk' => [
            'required' => 'ID KUK wajib diisi.',
            'integer' => 'ID KUK harus berupa angka.'
        ],
        'pertanyaan' => [
            'required' => 'Pertanyaan wajib diisi.'
        ],
        'jenis_jawaban' => [
            'required' => 'Jenis jawaban wajib diisi.',
            'in_list' => 'Jenis jawaban tidak valid.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['pmo_pertanyaan.pertanyaan', 'skema.nama_skema', 'unit.nama_unit'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('skema', 'skema.id_skema = pmo_pertanyaan.id_skema')
            ->join('unit', 'unit.id_unit = pmo_pertanyaan.id_unit')
            ->join('elemen', 'elemen.id_elemen = pmo_pertanyaan.id_elemen')
            ->join('kuk', 'kuk.id_kuk = pmo_pertanyaan.id_kuk')
            ->orderBy('skema.nama_skema, unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk, pmo_pertanyaan.urutan');
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('pmo_pertanyaan.*,
            skema.nama_skema,
            unit.nama_unit,
            elemen.nama_elemen,
            kuk.pertanyaan as kuk_pertanyaan,
            kuk.kode_kuk');
    }

    /**
     * Transform DataTable results if needed
     *
     * @param array $data Result data
     * @return array
     */
    protected function transformDataTableResults($data)
    {
        // Anda bisa mengubah data di sini jika diperlukan
        // Contoh: format tanggal, kalkulasi nilai, dll.
        return $data;
    }

    /**
     * Get all questions with related data
     */
    public function getAllWithRelations(): array
    {
        return $this->select('pmo_pertanyaan.*,
            skema.nama_skema,
            unit.nama_unit,
            elemen.nama_elemen,
            kuk.pertanyaan as kuk_pertanyaan,
            kuk.kode_kuk')
            ->join('skema', 'skema.id_skema = pmo_pertanyaan.id_skema')
            ->join('unit', 'unit.id_unit = pmo_pertanyaan.id_unit')
            ->join('elemen', 'elemen.id_elemen = pmo_pertanyaan.id_elemen')
            ->join('kuk', 'kuk.id_kuk = pmo_pertanyaan.id_kuk')
            ->orderBy('skema.nama_skema, unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk, pmo_pertanyaan.urutan')
            ->findAll();
    }

    /**
     * Get question by ID with full relations
     */
    public function getWithRelations(int $id_pertanyaan): ?array
    {
        return $this->select('pmo_pertanyaan.*,
            skema.nama_skema,
            unit.nama_unit,
            elemen.nama_elemen,
            kuk.pertanyaan as kuk_pertanyaan,
            kuk.kode_kuk')
            ->join('skema', 'skema.id_skema = pmo_pertanyaan.id_skema')
            ->join('unit', 'unit.id_unit = pmo_pertanyaan.id_unit')
            ->join('elemen', 'elemen.id_elemen = pmo_pertanyaan.id_elemen')
            ->join('kuk', 'kuk.id_kuk = pmo_pertanyaan.id_kuk')
            ->where('pmo_pertanyaan.id_pertanyaan', $id_pertanyaan)
            ->first();
    }

    /**
     * Get questions by Skema ID
     */
    public function getBySkema(int $id_skema): array
    {
        return $this->where('pmo_pertanyaan.id_skema', $id_skema)
            ->select('pmo_pertanyaan.*, unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk')
            ->join('unit', 'unit.id_unit = pmo_pertanyaan.id_unit')
            ->join('elemen', 'elemen.id_elemen = pmo_pertanyaan.id_elemen')
            ->join('kuk', 'kuk.id_kuk = pmo_pertanyaan.id_kuk')
            ->orderBy('unit.nama_unit, elemen.nama_elemen, kuk.kode_kuk, pmo_pertanyaan.urutan')
            ->findAll();
    }

    /**
     * Get questions by KUK ID
     */
    public function getByKuk(int $id_kuk): array
    {
        return $this->where('pmo_pertanyaan.id_kuk', $id_kuk)
            ->orderBy('pmo_pertanyaan.urutan')
            ->findAll();
    }

    /**
     * Get questions with choices
     */
    public function getPertanyaanWithPilihan(int $id_skema): array
    {
        $pertanyaan = $this->getBySkema($id_skema);
        $pilihanModel = new PmoPilihanJawabanModel();

        foreach ($pertanyaan as $key => $p) {
            if ($p['jenis_jawaban'] === 'PILIHAN_GANDA') {
                $pertanyaan[$key]['pilihan'] = $pilihanModel->where('id_pertanyaan', $p['id_pertanyaan'])->orderBy('urutan', 'ASC')->findAll();
            }
        }
        return $pertanyaan;
    }
}
