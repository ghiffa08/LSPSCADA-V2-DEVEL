<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesorSkemaModel extends Model
{
    protected $table            = 'asesor_skema';
    protected $primaryKey       = ['id_asesor', 'id_skema'];
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesor',
        'id_skema'
    ];

    // Validation
    protected $validationRules = [
        'id_asesor' => 'required|integer|is_not_unique[asesor.id_asesor]',
        'id_skema' => 'required|integer|is_not_unique[skema.id_skema]'
    ];

    protected $validationMessages = [
        'id_asesor' => [
            'required' => 'ID Asesor harus diisi',
            'integer' => 'ID Asesor harus berupa angka',
            'is_not_unique' => 'Asesor tidak ditemukan'
        ],
        'id_skema' => [
            'required' => 'ID Skema harus diisi',
            'integer' => 'ID Skema harus berupa angka',
            'is_not_unique' => 'Skema tidak ditemukan'
        ]
    ];

    /**
     * Get skemas for specific asesor
     */
    public function getSkemasByAsesor($id_asesor)
    {
        $builder = $this->db->table($this->table);
        $builder->select('s.id_skema, s.nama_skema, s.kode_skema');
        $builder->join('skema s', 's.id_skema = asesor_skema.id_skema');
        $builder->where('asesor_skema.id_asesor', $id_asesor);

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesors for specific skema
     */
    public function getAsesorsBySkema($id_skema)
    {
        $builder = $this->db->table($this->table);
        $builder->select('a.id_asesor, u.nama_lengkap, a.nomor_registrasi');
        $builder->join('asesor a', 'a.id_asesor = asesor_skema.id_asesor');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->where('asesor_skema.id_skema', $id_skema);
        $builder->where('u.active', 1);

        return $builder->get()->getResultArray();
    }

    /**
     * Check if asesor has competency in skema
     */
    public function hasCompetency($id_asesor, $id_skema)
    {
        $result = $this->where([
            'id_asesor' => $id_asesor,
            'id_skema' => $id_skema
        ])->first();

        return $result !== null;
    }

    /**
     * Add competency to asesor
     */
    public function addCompetency($id_asesor, $id_skema)
    {
        // Check if already exists
        if ($this->hasCompetency($id_asesor, $id_skema)) {
            return true;
        }

        return $this->insert([
            'id_asesor' => $id_asesor,
            'id_skema' => $id_skema
        ]);
    }

    /**
     * Remove competency from asesor
     */
    public function removeCompetency($id_asesor, $id_skema)
    {
        return $this->where([
            'id_asesor' => $id_asesor,
            'id_skema' => $id_skema
        ])->delete();
    }

    /**
     * Sync asesor competencies
     */
    public function syncAsesorSkemas($id_asesor, array $skemaIds)
    {
        $this->db->transBegin();

        try {
            // Delete existing
            $this->where('id_asesor', $id_asesor)->delete();

            // Insert new
            if (!empty($skemaIds)) {
                $batchData = [];
                foreach ($skemaIds as $id_skema) {
                    $batchData[] = [
                        'id_asesor' => $id_asesor,
                        'id_skema' => $id_skema
                    ];
                }
                $this->insertBatch($batchData);
            }

            $this->db->transCommit();
            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }
}
