<?php

namespace App\Models;

use CodeIgniter\Model;

class APL1Model extends Model
{
    protected $table            = 'apl1';
    protected $primaryKey       = 'id_apl1';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;
    protected $allowedFields    = [];

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

    public function getAllAPL1($id_siswa)
    {

        return $this->db->table('apl1')
            ->where('id_siswa', $id_siswa)
            ->join('wilayah_provinsi', 'wilayah_provinsi.id=apl1.provinsi', 'left')
            ->join('wilayah_kabupaten', 'wilayah_kabupaten.id=apl1.kabupaten', 'left')
            ->join('wilayah_kecamatan', 'wilayah_kecamatan.id=apl1.kecamatan', 'left')
            ->join('wilayah_desa', 'wilayah_desa.id=apl1.kelurahan', 'left')
            ->join('skema', 'skema.id_skema=apl1.id_skema', 'left')
            ->select('apl1.*,apl1.id_apl1, wilayah_provinsi.nama as nama_provinsi, wilayah_kabupaten.nama as nama_kabupaten, wilayah_kecamatan.nama as nama_kecamatan, wilayah_desa.nama as nama_kelurahan, skema.nama_skema, skema.id_skema as skema_id')
            ->Get()->getRowArray();
    }

    public function getAPL1byid($id)
    {

        return $this->db->table('apl1')
            ->where('id_apl1', $id)
            ->join('wilayah_provinsi', 'wilayah_provinsi.id=apl1.provinsi', 'left')
            ->join('wilayah_kabupaten', 'wilayah_kabupaten.id=apl1.kabupaten', 'left')
            ->join('wilayah_kecamatan', 'wilayah_kecamatan.id=apl1.kecamatan', 'left')
            ->join('wilayah_desa', 'wilayah_desa.id=apl1.kelurahan', 'left')
            ->join('skema', 'skema.id_skema=apl1.id_skema', 'left')
            ->select('apl1.*,apl1.id_apl1, wilayah_provinsi.nama as nama_provinsi, wilayah_kabupaten.nama as nama_kabupaten, wilayah_kecamatan.nama as nama_kecamatan, wilayah_desa.nama as nama_kelurahan, skema.nama_skema, skema.id_skema as skema_id')
            ->Get()->getRowArray();
    }


    public function getDataByIdSiswa($id_siswa)
    {
        return $this->where('id_siswa', $id_siswa)->findAll();
    }

    public function getUnvalidatedData()
    {
        return $this->where('validasi_apl1', 'N')->findAll();
    }

    public function getValidatedData()
    {
        return $this->where('validasi_apl1', 'Y')->findAll();
    }
}
