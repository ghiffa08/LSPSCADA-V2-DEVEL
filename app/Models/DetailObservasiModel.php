<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailObservasiModel extends Model
{
    protected $table            = 'detail_observasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields    = [
        'id_observasi',
        'id_skema',
        'id_kuk',
        'kompeten',
        'keterangan',
        'tanggal_observasi'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Validation rules
    protected $validationRules = [
        'id_observasi' => 'required|integer',
        'id_skema'     => 'required|integer',
        'id_kuk'       => 'required|integer',
        'kompeten'     => 'required|in_list[Y,N]',
        'keterangan'   => 'permit_empty|string|max_length[1000]'
    ];

    protected $validationMessages = [
        'id_observasi' => [
            'required' => 'ID Observasi harus diisi',
            'integer'  => 'ID Observasi harus berupa angka'
        ],
        'id_skema' => [
            'required' => 'ID Skema harus diisi',
            'integer'  => 'ID Skema harus berupa angka'
        ],
        'id_kuk' => [
            'required' => 'ID KUK harus diisi',
            'integer'  => 'ID KUK harus berupa angka'
        ],
        'kompeten' => [
            'required' => 'Status kompeten harus diisi',
            'in_list'  => 'Status kompeten harus Y atau N'
        ]
    ];

    protected $skipValidation = false;

    // Relationships
    public function observasi()
    {
        return $this->belongsTo(ObservasiModel::class, 'id_observasi', 'id_observasi');
    }

    /**
     * Get details with KUK information using eager loading
     */
    public function getDetailsWithKuk(int $id_observasi): array
    {
        return $this->select([
            'detail_observasi.*',
            'kuk.kode_kuk',
            'kuk.nama_kuk',
            'unit.kode_unit',
            'unit.nama_unit',
            'elemen.kode_elemen',
            'elemen.nama_elemen'
        ])
            ->join('kuk', 'kuk.id_kuk = detail_observasi.id_kuk', 'inner')
            ->join('elemen', 'elemen.id_elemen = kuk.id_elemen', 'inner')
            ->join('unit', 'unit.id_unit = elemen.id_unit', 'inner')
            ->where('detail_observasi.id_observasi', $id_observasi)
            ->orderBy('unit.kode_unit')
            ->orderBy('elemen.kode_elemen')
            ->orderBy('kuk.kode_kuk')
            ->findAll();
    }

    /**
     * Batch insert detail observasi (optimized for bulk insert)
     */
    public function batchInsertDetails(array $details): bool
    {
        if (empty($details)) {
            return false;
        }

        // Validate each item before batch insert
        foreach ($details as $detail) {
            if (!$this->validate($detail)) {
                return false;
            }
        }

        return $this->insertBatch($details);
    }

    /**
     * Update or insert detail observasi (upsert functionality)
     */
    public function upsertDetails(int $id_observasi, array $details): bool
    {
        if (empty($details)) {
            return false;
        }

        $db = $this->db;
        $db->transStart();

        try {
            // Delete existing details for this observation
            $this->where('id_observasi', $id_observasi)->delete();

            // Add id_observasi to each detail if not present
            $detailsWithObservasi = array_map(function ($detail) use ($id_observasi) {
                $detail['id_observasi'] = $id_observasi;
                return $detail;
            }, $details);

            // Batch insert new details
            $this->batchInsertDetails($detailsWithObservasi);

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in upsertDetails: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get summary statistics for observation
     */
    public function getObservationSummary(int $id_observasi): array
    {
        $total = $this->where('id_observasi', $id_observasi)->countAllResults();
        $kompeten = $this->where(['id_observasi' => $id_observasi, 'kompeten' => 'Y'])->countAllResults();
        $belum_kompeten = $this->where(['id_observasi' => $id_observasi, 'kompeten' => 'N'])->countAllResults();

        return [
            'total_kuk' => $total,
            'kompeten' => $kompeten,
            'belum_kompeten' => $belum_kompeten,
            'persentase_kompeten' => $total > 0 ? round(($kompeten / $total) * 100, 2) : 0
        ];
    }
}
