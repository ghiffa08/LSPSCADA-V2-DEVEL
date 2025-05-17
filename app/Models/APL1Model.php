<?php

namespace App\Models;

use CodeIgniter\Model;

class APL1Model extends Model
{
    protected $table            = 'apl1';
    protected $primaryKey       = 'id_apl1';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    // protected $allowedFields    = [
    //     'id_apl1',
    //     'email',
    //     'nama_siswa',
    //     'nik',
    //     'tempat_lahir',
    //     'tanggal_lahir',
    //     'jenis_kelamin',
    //     'pendidikan_terakhir',
    //     'nama_sekolah',
    //     'jurusan',
    //     'kebangsaan',
    //     'provinsi',
    //     'kabupaten',
    //     'kecamatan',
    //     'kelurahan',
    //     'rt',
    //     'rw',
    //     'kode_pos',
    //     'telpon_rumah',
    //     'no_hp',
    //     'pekerjaan',
    //     'nama_lembaga',
    //     'alamat_perusahaan',
    //     'jabatan',
    //     'email_perusahaan',
    //     'no_telp_perusahaan',
    //     'id_asesmen',
    //     'pas_foto',
    //     'ktp',
    //     'bukti_pendidikan',
    //     'tanda_tangan_asesi',
    //     'raport',
    //     'sertifikat_pkl',
    //     'validasi_apl1',
    //     'created_at',
    //     'updated_at',
    //     'deleted_at'
    // ];

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

    public function findAllAPL1()
    {

        return $this->db->table('apl1')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->select('apl1.*,apl1.id_apl1, apl1.updated_at as tanggal_validasi, skema.nama_skema, skema.id_skema as skema_id ')
            ->Get()->getResultArray();
    }

    public function getAPL1($id)
    {

        return $this->db->table('apl1')
            ->where('apl1.id_apl1', $id)
            ->join('wilayah_provinsi', 'wilayah_provinsi.id=apl1.provinsi', 'left')
            ->join('wilayah_kabupaten', 'wilayah_kabupaten.id=apl1.kabupaten', 'left')
            ->join('wilayah_kecamatan', 'wilayah_kecamatan.id=apl1.kecamatan', 'left')
            ->join('wilayah_desa', 'wilayah_desa.id=apl1.kelurahan', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('users as admin_users', 'admin_users.id=apl1.validasi_admin', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->select('apl1.*,apl1.id_apl1, wilayah_provinsi.nama as nama_provinsi, wilayah_kabupaten.nama as nama_kabupaten, wilayah_kecamatan.nama as nama_kecamatan, wilayah_desa.nama as nama_kelurahan, skema.nama_skema, skema.id_skema as skema_id, skema.jenis_skema, asesmen.tujuan,admin_users.fullname as validator_apl1, admin_users.tanda_tangan as ttd_validator_apl1')
            ->Get()->getRowArray();
    }

    public function getEmailValidasiToday()
    {

        return $this->db->table('apl1')
            ->whereIn('validasi_apl1', ['validated', 'unvalid'])
            ->where('DATE(apl1.updated_at)', date('Y-m-d'))
            ->where('apl1.email_validasi', 0)
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'left')
            ->join('users as admin_users', 'admin_users.id = apl1.validasi_admin', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('apl1.id_apl1, apl1.validasi_apl1, apl1.nama_siswa, apl1.email, apl1.email_validasi as email_validasi_apl1, apl1.updated_at as tanggal_validasi, skema.nama_skema, skema.id_skema, admin_users.fullname as validator_apl1')
            ->get()
            ->getResultArray();
    }

    public function getEmailValidasiByDate($date)
    {

        return $this->db->table('apl1')
            ->whereIn('validasi_apl1', ['validated', 'unvalid'])
            ->where('DATE(apl1.updated_at)', $date)
            ->where('apl1.email_validasi', 0)
            ->join('asesmen', 'asesmen.id_asesmen = apl1.id_asesmen', 'left')
            ->join('users as admin_users', 'admin_users.id = apl1.validasi_admin', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('apl1.id_apl1, apl1.validasi_apl1, apl1.nama_siswa, apl1.email, apl1.email_validasi as email_validasi_apl1, apl1.updated_at as tanggal_validasi, skema.nama_skema, skema.id_skema, admin_users.fullname as validator_apl1')
            ->get()
            ->getResultArray();
    }

    public function getbyttdAsesi($ttd)
    {

        return $this->db->table('apl1')
            ->where('tanda_tangan_asesi', $ttd)
            ->join('users', 'users.id=apl1.validasi_admin', 'left')
            ->select('apl1.nama_siswa, apl1.created_at, apl1.tanda_tangan_asesi, users.tanda_tangan as tanda_tangan_validator, users.fullname')
            ->Get()->getRowArray();
    }

    public function getbyttdAdmin($ttd)
    {

        return $this->db->table('apl1')
            ->where('users.tanda_tangan', $ttd)
            ->join('users', 'users.id=apl1.validasi_admin', 'left')
            ->select('users.tanda_tangan as tanda_tangan_validator, users.fullname as nama_validator, apl1.created_at')
            ->Get()->getRowArray();
    }

    public function getUnvalidatedData()
    {
        return $this->db->table('apl1')
            ->where('validasi_apl1', 'unvalid')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->orderBy('created_at', 'DESC')
            ->select('apl1.id_apl1,apl1.nama_siswa, apl1.validasi_apl1, apl1.email, apl1.pas_foto ,  apl1.tanda_tangan_asesi,  apl1.bukti_pendidikan,  apl1.ktp,  apl1.raport,  apl1.sertifikat_pkl,skema.nama_skema, skema.id_skema as skema_id')
            ->Get()->getResultArray();
    }

    public function getPendingData()
    {
        return $this->db->table('apl1')
            ->where('validasi_apl1', 'pending')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->orderBy('created_at', 'DESC')
            ->select('apl1.id_apl1,apl1.nama_siswa, apl1.validasi_apl1, apl1.email, apl1.pas_foto ,  apl1.tanda_tangan_asesi,  apl1.bukti_pendidikan,  apl1.ktp,  apl1.raport,  apl1.sertifikat_pkl,skema.nama_skema, skema.id_skema as skema_id')
            ->Get()->getResultArray();
    }


    public function getValidatedData()
    {
        return $this->db->table('apl1')
            ->where('validasi_apl1', 'validated')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->orderBy('created_at', 'DESC')
            ->select('apl1.id_apl1,apl1.nama_siswa, apl1.validasi_apl1, apl1.email, apl1.pas_foto ,  apl1.tanda_tangan_asesi,  apl1.bukti_pendidikan,  apl1.ktp,  apl1.raport,  apl1.sertifikat_pkl,skema.nama_skema, skema.id_skema as skema_id')
            ->Get()->getResultArray();
    }

    public function getMonitoring()
    {
        return $this->db->table('apl1')
            ->join('apl2', 'apl2.id_apl1=apl1.id_apl1', 'left')
            ->join('asesmen', 'asesmen.id_asesmen=apl1.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as admin_users', 'admin_users.id=apl1.validasi_admin', 'left')
            ->join('users as asesor_users', 'asesor_users.id=apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('apl1.id_apl1,skema.nama_skema, apl1.nama_siswa, apl1.validasi_apl1 as status_apl1, apl1.pas_foto, apl1.ktp, apl1.bukti_pendidikan, apl1.tanda_tangan_asesi, apl1.raport, apl1.sertifikat_pkl, admin_users.fullname as validator_apl1, apl1.email_validasi as email_apl1, apl2.id_apl2, apl2.validasi_apl2 as status_apl2, apl2.email_validasi as email_apl2, asesor_users.fullname as validator_apl2')
            ->get()
            ->getResultArray();
    }


    /**
     * Get application by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getApplicationById(string $id): ?array
    {
        return $this->find($id);
    }

    /**
     * Get application by email
     *
     * @param string $email
     * @return array|null
     */
    public function getApplicationByEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get application by NIK
     *
     * @param string $nik
     * @return array|null
     */
    public function getApplicationByNik(string $nik): ?array
    {
        return $this->where('nik', $nik)->first();
    }

    /**
     * Get pending applications
     *
     * @return array
     */
    public function getPendingApplications(): array
    {
        return $this->where('validasi_apl1', 'pending')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Update application status
     *
     * @param string $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $id, string $status): bool
    {
        return $this->update($id, ['validasi_apl1' => $status]);
    }

    /**
     * Count applications by status
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return $this->where('validasi_apl1', $status)->countAllResults();
    }
}
