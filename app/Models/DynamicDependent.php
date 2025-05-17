<?php

namespace App\Models;

use CodeIgniter\Model;

class DynamicDependent extends Model
{
    public function AllProvinsi()
    {
        return $this->db->table('wilayah_provinsi')->Get()->getResultArray();
    }

    public function getAllKabupaten($id_provinsi)
    {
        return $this->db->table('wilayah_kabupaten')->where('provinsi_id', $id_provinsi)->Get()->getResultArray();
    }

    public function getAllKecamatan($id_kabupaten)
    {
        return $this->db->table('wilayah_kecamatan')->where('kabupaten_id', $id_kabupaten)->Get()->getResultArray();
    }

    public function getAllDesa($id_kecamatan)
    {
        return $this->db->table('wilayah_desa')->where('kecamatan_id', $id_kecamatan)->Get()->getResultArray();
    }
}
