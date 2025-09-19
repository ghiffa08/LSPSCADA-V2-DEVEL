<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class PertanyaanTertulisSoalModel extends Model
{
    use DataTableTrait;

    protected $table            = 'pertanyaan_tertulis_soal';
    protected $primaryKey       = 'id_soal';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_skema',
        'soal',
        'jenis_soal',
        'urutan',
        'aktif'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'id_skema' => 'required|integer',
        'soal' => 'required',
        'jenis_soal' => 'required|in_list[PILIHAN_GANDA,ESSAY,BENAR_SALAH]',
        'urutan' => 'permit_empty|integer',
        'aktif' => 'in_list[Y,N]'
    ];

    protected $validationMessages = [
        'id_skema' => [
            'required' => 'Skema wajib dipilih.',
            'integer' => 'ID Skema harus berupa angka.'
        ],
        'soal' => [
            'required' => 'Teks soal wajib diisi.'
        ],
        'jenis_soal' => [
            'required' => 'Jenis soal wajib dipilih.',
            'in_list' => 'Jenis soal tidak valid.'
        ]
    ];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['pertanyaan_tertulis_soal.soal', 'skema.nama_skema'];

    /**
     * Menerapkan join untuk query DataTable
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('skema', 'skema.id_skema = pertanyaan_tertulis_soal.id_skema');
    }

    /**
     * Menerapkan field select kustom untuk query DataTable
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('pertanyaan_tertulis_soal.*, skema.nama_skema');
    }

    /**
     * Mengambil semua soal dengan relasi ke skema
     */
    public function getAllWithRelations(): array
    {
        return $this->select('pertanyaan_tertulis_soal.*, skema.nama_skema')
            ->join('skema', 'skema.id_skema = pertanyaan_tertulis_soal.id_skema')
            ->orderBy('skema.nama_skema, pertanyaan_tertulis_soal.urutan')
            ->findAll();
    }

    /**
     * Mengambil soal berdasarkan ID dengan relasi
     */
    public function getWithRelations(int $id_soal): ?array
    {
        return $this->select('pertanyaan_tertulis_soal.*, skema.nama_skema')
            ->join('skema', 'skema.id_skema = pertanyaan_tertulis_soal.id_skema')
            ->where('pertanyaan_tertulis_soal.id_soal', $id_soal)
            ->first();
    }

    /**
     * Mengambil soal berdasarkan ID Skema
     */
    public function getBySkema(int $id_skema): array
    {
        return $this->where('id_skema', $id_skema)
            ->where('aktif', 'Y')
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }
}
