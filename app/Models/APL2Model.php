<?php

namespace App\Models;

use CodeIgniter\Model;

class APL2Model extends Model
{
    protected $table            = 'apl2';
    protected $primaryKey       = 'id_apl2';
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
    protected $useTimestamps = true;
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

    public function findAllAPL2()
    {

        return $this->db->table('apl2')
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->select('apl1.id_apl1, apl1.nama_siswa, apl2.id_apl2, apl2.validasi_apl2, skema.nama_skema, skema.id_skema as skema_id')
            ->Get()->getResultArray();
    }

    public function getEmailValidasiToday()
    {

        return $this->db->table('apl2')
            ->whereIn('validasi_apl2', ['validated', 'unvalid'])
            ->where('DATE(apl2.updated_at)', date('Y-m-d'))
            ->where('apl2.email_validasi', 0)
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('apl1.id_apl1, apl2.id_apl2, apl2.validasi_apl2, apl1.nama_siswa, apl1.email, apl2.email_validasi as email_validasi_apl2, apl2.updated_at as tanggal_validasi, skema.nama_skema, skema.id_skema, asesor_users.fullname as validator_apl2')
            ->get()
            ->getResultArray();
    }

    public function getEmailValidasiByDate($date)
    {

        return $this->db->table('apl2')
            ->whereIn('validasi_apl2', ['validated', 'unvalid'])
            ->where('DATE(apl2.updated_at)', $date)
            ->where('apl2.email_validasi', 0)
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('apl1.id_apl1, apl2.id_apl2, apl2.validasi_apl2, apl1.nama_siswa, apl1.email, apl2.email_validasi as email_validasi_apl2, apl2.updated_at as tanggal_validasi, skema.nama_skema, skema.id_skema, asesor_users.fullname as validator_apl2')
            ->get()
            ->getResultArray();
    }

    public function getbySkema($id_skema)
    {
        return $this->db->table('apl2')
            ->where('apl2_jawaban.id_skema', $id_skema)
            ->join('apl2_jawaban', 'apl2_jawaban.kode_jawaban_apl2=apl2.kode_jawaban_apl2', 'left')
            ->join('skema', 'skema.id_skema=apl2_jawaban.id_skema', 'left')
            ->join('unit', 'unit.id_unit=apl2_jawaban.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=apl2_jawaban.id_elemen', 'left')
            ->join('subelemen', 'subelemen.id_subelemen=apl2_jawaban.id_subelemen', 'left')
            ->select('apl2.id_apl1,apl2_jawaban.kode_jawaban_apl2,apl2_jawaban.bukti_pendukung,apl2_jawaban.tk, skema.nama_skema, unit.id_unit, unit.nama_unit, elemen.id_elemen, elemen.nama_elemen,subelemen.id_subelemen, subelemen.pertanyaan')
            ->Get()->getResultArray();
    }

    public function getAllAsesmen($id)
    {

        return $this->db->table('apl2')
            ->where('apl1.id_apl1', $id)
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('wilayah_provinsi', 'wilayah_provinsi.id=apl1.provinsi', 'left')
            ->join('wilayah_kabupaten', 'wilayah_kabupaten.id=apl1.kabupaten', 'left')
            ->join('wilayah_kecamatan', 'wilayah_kecamatan.id=apl1.kecamatan', 'left')
            ->join('wilayah_desa', 'wilayah_desa.id=apl1.kelurahan', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('users as admin_users', 'admin_users.id=apl1.validasi_admin', 'left')
            ->join('users as asesor_users', 'asesor_users.id=apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->select('apl1.*,apl1.id_apl1, wilayah_provinsi.nama as nama_provinsi, wilayah_kabupaten.nama as nama_kabupaten, wilayah_kecamatan.nama as nama_kecamatan, wilayah_desa.nama as nama_kelurahan, skema.nama_skema, skema.id_skema as skema_id, skema.jenis_skema, asesmen.tujuan,admin_users.fullname as validator_apl1, admin_users.tanda_tangan as ttd_validator_apl1, asesor_users.fullname as validator_apl2, asesor_users.tanda_tangan as ttd_validator_apl2, apl2.validasi_apl2')
            ->Get()->getRowArray();
    }

    public function persetujuanAsesmen($id)
    {

        return $this->db->table('apl2')
            ->where('apl1.id_apl1', $id)
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as asesor_users', 'asesor_users.id=apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('apl1.id_apl1,skema.nama_skema, skema.jenis_skema, tuk.nama_tuk, tuk.jenis_tuk, apl1.nama_siswa, apl1.tanda_tangan_asesi, apl1.created_at,apl2.updated_at, set_tanggal.tanggal, apl2.id_apl2, asesor_users.fullname as validator,asesor_users.tanda_tangan as ttd_validator')
            ->get()
            ->getRowArray();
    }

    public function AllpersetujuanAsesmen()
    {
        return $this->db->table('apl2')
            ->where('validasi_apl1', 'validated')
            ->where('validasi_apl2', 'validated')
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as asesor_users', 'asesor_users.id=apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('apl1.id_apl1,skema.nama_skema, skema.jenis_skema, tuk.nama_tuk, tuk.jenis_tuk, apl1.nama_siswa, apl1.tanda_tangan_asesi, apl1.created_at,apl2.updated_at, set_tanggal.tanggal, apl2.id_apl2, asesor_users.fullname as validator,asesor_users.tanda_tangan as ttd_validator')
            ->get()
            ->getResultArray();
    }

    public function getbyId($id)
    {
        return $this->db->table('apl2')
            ->where('id_apl1', $id)
            ->join('apl2_jawaban', 'apl2_jawaban.kode_jawaban_apl2=apl2.kode_jawaban_apl2', 'left')
            ->join('skema', 'skema.id_skema=apl2_jawaban.id_skema', 'left')
            ->join('unit', 'unit.id_unit=apl2_jawaban.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=apl2_jawaban.id_elemen', 'left')
            ->join('subelemen', 'subelemen.id_subelemen=apl2_jawaban.id_subelemen', 'left')
            ->select('apl2.id_apl1,apl2_jawaban.kode_jawaban_apl2,apl2_jawaban.bukti_pendukung,apl2_jawaban.tk, skema.nama_skema, unit.id_unit, unit.nama_unit, elemen.id_elemen, elemen.nama_elemen,subelemen.id_subelemen, subelemen.pertanyaan')
            ->Get()->getResultArray();
    }

    public function getAll()
    {
        return $this->db->table('apl2')
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->select('apl2.validasi_apl2, apl2.id_apl2, apl2.kode_jawaban_apl2, apl1.id_apl1,apl1.email, apl1.nama_siswa, skema.nama_skema')
            ->Get()->getResultArray();
    }

    public function getPendingData2()
    {
        return $this->db->table('apl2')
            ->where('validasi_apl2', 'pending')
            ->join('apl1', 'apl1.id_apl1 = apl2.id_apl1', 'left')
            ->orderBy('apl2.created_at', 'DESC')
            ->select('apl1.id_apl1, apl1.nama_siswa, apl1.email, apl2.id_apl2, apl2.kode_jawaban_apl2, apl2.validasi_apl2')
            ->get()->getResultArray();
    }

    public function emailDetailSertifikasi($id)
    {

        return $this->db->table('apl2')
            ->where('apl1.id_apl1', $id)
            ->join('apl1', 'apl1.id_apl1=apl2.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as admin_users', 'admin_users.id=apl1.validasi_admin', 'left')
            ->join('users as asesor_users', 'asesor_users.id=apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('apl1.id_apl1,skema.nama_skema, skema.jenis_skema, tuk.nama_tuk, tuk.jenis_tuk, apl1.nama_siswa, apl1.tanda_tangan_asesi, apl1.created_at, set_tanggal.tanggal, admin_users.fullname as valdator_apl1, apl2.id_apl2, asesor_users.fullname as validator_apl2')
            ->get()
            ->getRowArray();
    }


    public function getbyttdAsesor($ttd)
    {

        return $this->db->table('apl2')
            ->where('tanda_tangan', $ttd)
            ->join('users', 'users.id=apl2.validator', 'left')
            ->select('users.tanda_tangan as tanda_tangan_validator, users.fullname, apl2.created_at')
            ->Get()->getRowArray();
    }
}
