<?php
// app/Models/PMOPertanyaanModel.php

namespace App\Models;

use CodeIgniter\Model;

class PMOPertanyaanModel extends Model
{
    protected $table = 'pmo_template_pertanyaan';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'id_unit', 'kuk_reference', 'pertanyaan', 'jenis_jawaban',
        'pilihan_jawaban', 'urutan', 'is_active'
    ];

    protected $validationRules = [
        'id_unit' => 'required|integer',
        'pertanyaan' => 'required|min_length[10]|max_length[1000]',
        'jenis_jawaban' => 'required|in_list[ya_tidak,pilihan_ganda,essay]'
    ];

    protected $validationMessages = [
        'id_unit' => [
            'required' => 'Unit kompetensi harus dipilih',
            'integer' => 'Unit kompetensi tidak valid'
        ],
        'pertanyaan' => [
            'required' => 'Pertanyaan harus diisi',
            'min_length' => 'Pertanyaan minimal 10 karakter',
            'max_length' => 'Pertanyaan maksimal 1000 karakter'
        ],
        'jenis_jawaban' => [
            'required' => 'Jenis jawaban harus dipilih',
            'in_list' => 'Jenis jawaban tidak valid'
        ]
    ];

    /**
     * Get pertanyaan with unit details
     */
    public function getPertanyaanWithUnit($id = null)
    {
        $builder = $this->db->table($this->table . ' ptp')
            ->select('
                ptp.id,
                ptp.id_unit,
                ptp.kuk_reference,
                ptp.pertanyaan,
                ptp.jenis_jawaban,
                ptp.pilihan_jawaban,
                ptp.urutan,
                ptp.is_active,
                ptp.created_at,
                ptp.updated_at,
                u.kode_unit,
                u.nama_unit,
                s.nama_skema,
                s.kode_skema
            ')
            ->join('unit u', 'u.id_unit = ptp.id_unit')
            ->join('skema s', 's.id_skema = u.id_skema');

        if ($id) {
            return $builder->where('ptp.id', $id)->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get pertanyaan by unit
     */
    public function getPertanyaanByUnit($id_unit)
    {
        return $this->where('id_unit', $id_unit)
            ->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Get pertanyaan by skema
     */
    public function getPertanyaanBySkema($id_skema)
    {
        return $this->db->table($this->table . ' ptp')
            ->select('
                ptp.id,
                ptp.id_unit,
                ptp.kuk_reference,
                ptp.pertanyaan,
                ptp.jenis_jawaban,
                ptp.pilihan_jawaban,
                ptp.urutan,
                u.kode_unit,
                u.nama_unit
            ')
            ->join('unit u', 'u.id_unit = ptp.id_unit')
            ->where('u.id_skema', $id_skema)
            ->where('ptp.is_active', 1)
            ->orderBy('u.id_unit', 'ASC')
            ->orderBy('ptp.urutan', 'ASC')
            ->orderBy('ptp.id', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get data for DataTable
     */
    public function getDataTable($start = 0, $length = 10, $search = '', $orderBy = 'id', $orderDir = 'asc')
    {
        $builder = $this->db->table($this->table . ' ptp')
            ->select('
                ptp.id,
                ptp.id_unit,
                ptp.kuk_reference,
                ptp.pertanyaan,
                ptp.jenis_jawaban,
                ptp.urutan,
                ptp.is_active,
                ptp.created_at,
                u.kode_unit,
                u.nama_unit,
                s.nama_skema
            ')
            ->join('unit u', 'u.id_unit = ptp.id_unit')
            ->join('skema s', 's.id_skema = u.id_skema');

        // Total records
        $total = $builder->countAllResults(false);

        // Search
        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.kode_unit', $search)
                ->orLike('u.nama_unit', $search)
                ->orLike('ptp.pertanyaan', $search)
                ->orLike('ptp.kuk_reference', $search)
                ->orLike('s.nama_skema', $search)
                ->groupEnd();
        }

        // Filtered records
        $filtered = $builder->countAllResults(false);

        // Order and limit
        $builder->orderBy($orderBy, $orderDir)
            ->limit($length, $start);

        $data = $builder->get()->getResultArray();

        return [
            'data' => $data,
            'total' => $total,
            'filtered' => $filtered
        ];
    }

    /**
     * Generate default pertanyaan for unit
     */
    public function generateDefaultPertanyaan($id_pmo, $id_skema)
    {
        $unitModel = model('UnitModel');
        $units = $unitModel->where('id_skema', $id_skema)
            ->where('status', 'Y')
            ->findAll();

        $pertanyaanData = [];
        $urutan = 1;

        foreach ($units as $unit) {
            // Generate default pertanyaan untuk setiap unit
            $pertanyaanData[] = [
                'id_unit' => $unit['id_unit'],
                'pertanyaan' => "Apakah asesi mampu mendemonstrasikan kompetensi unit: {$unit['nama_unit']}?",
                'jenis_jawaban' => 'ya_tidak',
                'urutan' => $urutan++,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Tambah pertanyaan aspek kritis
            $pertanyaanData[] = [
                'id_unit' => $unit['id_unit'],
                'pertanyaan' => "Apakah asesi memahami aspek kritis dalam pelaksanaan unit: {$unit['nama_unit']}?",
                'jenis_jawaban' => 'ya_tidak',
                'urutan' => $urutan++,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($pertanyaanData)) {
            return $this->insertBatch($pertanyaanData);
        }

        return true;
    }

    /**
     * Clone pertanyaan from another skema
     */
    public function clonePertanyaanFromSkema($target_skema_id, $source_skema_id)
    {
        // Get unit mapping
        $unitModel = model('UnitModel');
        $targetUnits = $unitModel->where('id_skema', $target_skema_id)->findAll();
        $sourceUnits = $unitModel->where('id_skema', $source_skema_id)->findAll();

        // Create mapping by kode_unit
        $unitMapping = [];
        foreach ($targetUnits as $targetUnit) {
            foreach ($sourceUnits as $sourceUnit) {
                if ($targetUnit['kode_unit'] === $sourceUnit['kode_unit']) {
                    $unitMapping[$sourceUnit['id_unit']] = $targetUnit['id_unit'];
                    break;
                }
            }
        }

        if (empty($unitMapping)) {
            return false;
        }

        // Get source pertanyaan
        $sourcePertanyaan = $this->getPertanyaanBySkema($source_skema_id);
        
        $clonedData = [];
        foreach ($sourcePertanyaan as $pertanyaan) {
            if (isset($unitMapping[$pertanyaan['id_unit']])) {
                $clonedData[] = [
                    'id_unit' => $unitMapping[$pertanyaan['id_unit']],
                    'kuk_reference' => $pertanyaan['kuk_reference'],
                    'pertanyaan' => $pertanyaan['pertanyaan'],
                    'jenis_jawaban' => $pertanyaan['jenis_jawaban'],
                    'pilihan_jawaban' => $pertanyaan['pilihan_jawaban'],
                    'urutan' => $pertanyaan['urutan'],
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
        }

        if (!empty($clonedData)) {
            return $this->insertBatch($clonedData);
        }

        return true;
    }
}
