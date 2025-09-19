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
    protected $allowedFields    = ['id_apl2', 'kode_jawaban_apl2', 'id_pengajuan', 'validasi_apl2', 'validator', 'email_validasi'];

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

    /**
     * Mengambil data APL2 dan jawabannya berdasarkan ID Pengajuan.
     * Fungsi ini sudah benar dan tidak memerlukan perubahan.
     */
    public function getByPengajuanId($id_pengajuan)
    {
        return $this->db->table('apl2')
            ->where('id_pengajuan', $id_pengajuan)
            ->join('apl2_jawaban', 'apl2_jawaban.kode_jawaban_apl2=apl2.kode_jawaban_apl2', 'left')
            ->join('skema', 'skema.id_skema=apl2_jawaban.id_skema', 'left')
            ->join('unit', 'unit.id_unit=apl2_jawaban.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=apl2_jawaban.id_elemen', 'left')
            ->join('kuk', 'kuk.id_kuk=apl2_jawaban.id_kuk', 'left') // Join ke tabel KUK
            ->select('
                apl2.id_pengajuan,
                apl2_jawaban.kode_jawaban_apl2,
                apl2_jawaban.bukti_pendukung,
                apl2_jawaban.tk,
                skema.nama_skema,
                unit.id_unit,
                unit.nama_unit,
                elemen.id_elemen,
                elemen.nama_elemen,
                kuk.id_kuk,
                kuk.pertanyaan
            ')
            ->get()->getResultArray();
    }

    /**
     * FUNGSI DIPERBARUI: Mengambil data Asesmen Mandiri dengan filter.
     * Lebih logis, efisien, dan fleksibel dengan parameter filter.
     */
    public function getByUserId($id_user, $filter = 'terbaru')
    {
        $builder = $this->db->table('pengajuan_asesmen pa');
        $builder->select('
            pa.id_pengajuan,
            skema.nama_skema,
            skema.jenis_skema,
            apl2.id_apl2,
            apl2.validasi_apl2,
            apl2.updated_at as updated_at_apl2,
            pa.created_at
        ')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen')
            ->join('skema', 'skema.id_skema = asesmen.id_skema')
            // Gunakan LEFT JOIN agar asesmen yang belum dikerjakan (belum ada APL2) tetap muncul
            ->join('apl2', 'apl2.id_pengajuan = pa.id_pengajuan', 'left')
            ->where('asesi.id_user', $id_user)
            // Tambahkan filter ini agar hanya pengajuan yang sudah diterima yang tampil
            ->where('pa.status_pengajuan', 'diterima');

        // Logika untuk filter pengurutan data
        switch ($filter) {
            case 'terlama':
                $builder->orderBy('pa.created_at', 'ASC');
                break;
            case 'status':
                $builder->orderBy("CASE 
                WHEN apl2.validasi_apl2 = 'unvalid' THEN 1
                WHEN apl2.id_apl2 IS NOT NULL AND apl2.validasi_apl2 IS NULL THEN 2
                WHEN apl2.id_apl2 IS NULL THEN 3
                WHEN apl2.validasi_apl2 = 'validated' THEN 4
                ELSE 5
            END", 'ASC')->orderBy('pa.created_at', 'DESC');
                break;
            case 'terbaru':
            default:
                $builder->orderBy('pa.created_at', 'DESC');
                break;
        }

        return $builder->get()->getResultArray();
    }


    /**
     * Mengambil detail asesmen untuk satu pengajuan spesifik.
     * Relasi diubah dari apl1 ke pengajuan_asesmen.
     */
    public function getAllAsesmen($id_pengajuan)
    {
        return $this->db->table('apl2')
            ->where('apl2.id_pengajuan', $id_pengajuan)
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->select('
                pa.id_pengajuan,
                asesi.*,
                users.nama_lengkap as nama_siswa, users.email,
                skema.nama_skema, skema.id_skema as skema_id, skema.jenis_skema,
                asesmen.tujuan,
                asesor_users.nama_lengkap as validator_apl2,
                asesor_users.tanda_tangan as ttd_validator_apl2,
                apl2.validasi_apl2
            ')
            ->get()->getRowArray();
    }

    /**
     * Mengambil semua data APL2 dengan informasi skema dan nama asesi.
     * PENYESUAIAN: Join diubah dari apl1 ke pengajuan_asesmen -> asesi -> users.
     */
    public function findAllAPL2()
    {
        return $this->db->table('apl2')
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('
                pa.id_pengajuan,
                users.nama_lengkap as nama_siswa,
                apl2.id_apl2,
                apl2.validasi_apl2,
                skema.nama_skema,
                skema.id_skema as skema_id
            ')
            ->get()->getResultArray();
    }

    /**
     * Mengambil data untuk notifikasi email validasi hari ini.
     * PENYESUAIAN: Join diubah dari apl1 ke pengajuan_asesmen -> asesi -> users.
     */
    public function getEmailValidasiToday()
    {
        return $this->db->table('apl2')
            ->whereIn('validasi_apl2', ['validated', 'unvalid'])
            ->where('DATE(apl2.updated_at)', date('Y-m-d'))
            ->where('apl2.email_validasi', 0)
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('
                pa.id_pengajuan,
                apl2.id_apl2,
                apl2.validasi_apl2,
                users.nama_lengkap as nama_siswa,
                users.email,
                apl2.email_validasi as email_validasi_apl2,
                apl2.updated_at as tanggal_validasi,
                skema.nama_skema,
                skema.id_skema,
                asesor_users.nama_lengkap as validator_apl2
            ')
            ->get()->getResultArray();
    }

    /**
     * Mengambil data untuk notifikasi email validasi berdasarkan tanggal.
     * PENYESUAIAN: Join diubah dari apl1 ke pengajuan_asesmen -> asesi -> users.
     */
    public function getEmailValidasiByDate($date)
    {
        return $this->db->table('apl2')
            ->whereIn('validasi_apl2', ['validated', 'unvalid'])
            ->where('DATE(apl2.updated_at)', $date)
            ->where('apl2.email_validasi', 0)
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('
                pa.id_pengajuan,
                apl2.id_apl2,
                apl2.validasi_apl2,
                users.nama_lengkap as nama_siswa,
                users.email,
                apl2.email_validasi as email_validasi_apl2,
                apl2.updated_at as tanggal_validasi,
                skema.nama_skema,
                skema.id_skema,
                asesor_users.nama_lengkap as validator_apl2
            ')
            ->get()->getResultArray();
    }

    /**
     * Mengambil detail persetujuan asesmen berdasarkan ID pengajuan.
     * PENYESUAIAN: Parameter diubah menjadi $id_pengajuan dan join disesuaikan.
     */
    public function persetujuanAsesmen($id_pengajuan)
    {
        return $this->db->table('apl2')
            ->where('pa.id_pengajuan', $id_pengajuan)
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('
                pa.id_pengajuan,
                skema.nama_skema,
                skema.jenis_skema,
                tuk.nama_tuk,
                tuk.jenis_tuk,
                users.nama_lengkap as nama_siswa,
                asesi.tanda_tangan_asesi,
                pa.created_at,
                apl2.updated_at,
                set_tanggal.tanggal,
                apl2.id_apl2,
                asesor_users.nama_lengkap as validator,
                asesor_users.tanda_tangan as ttd_validator
            ')
            ->get()->getRowArray();
    }

    /**
     * Mengambil semua data persetujuan asesmen yang sudah divalidasi.
     * PENYESUAIAN: Kondisi 'validasi_apl1' diganti dengan status pengajuan 'diterima'.
     */
    public function AllpersetujuanAsesmen()
    {
        return $this->db->table('apl2')
            ->where('pa.status_pengajuan', 'diterima') // Asumsi 'validated' apl1 setara 'diterima'
            ->where('apl2.validasi_apl2', 'validated')
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('
                pa.id_pengajuan,
                skema.nama_skema,
                skema.jenis_skema,
                tuk.nama_tuk,
                tuk.jenis_tuk,
                users.nama_lengkap as nama_siswa,
                asesi.tanda_tangan_asesi,
                pa.created_at,
                apl2.updated_at,
                set_tanggal.tanggal,
                apl2.id_apl2,
                asesor_users.nama_lengkap as validator,
                asesor_users.tanda_tangan as ttd_validator
            ')
            ->get()->getResultArray();
    }

    /**
     * Mengambil semua data APL2 dengan informasi dasar.
     * PENYESUAIAN: Join diubah dari apl1 ke pengajuan_asesmen -> asesi -> users.
     */
    public function getAll()
    {
        return $this->db->table('apl2')
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('skema', 'skema.id_skema = asesmen.id_skema', 'left')
            ->select('
                apl2.validasi_apl2,
                apl2.id_apl2,
                apl2.kode_jawaban_apl2,
                pa.id_pengajuan,
                users.email,
                users.nama_lengkap as nama_siswa,
                skema.nama_skema
            ')
            ->get()->getResultArray();
    }

    /**
     * Mengambil data APL2 yang statusnya masih 'pending'.
     * PENYESUAIAN: Join diubah dari apl1 ke pengajuan_asesmen -> asesi -> users.
     */
    public function getPendingData2()
    {
        return $this->db->table('apl2')
            ->where('validasi_apl2', 'pending')
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->orderBy('apl2.created_at', 'DESC')
            ->select('
                pa.id_pengajuan,
                users.nama_lengkap as nama_siswa,
                users.email,
                apl2.id_apl2,
                apl2.kode_jawaban_apl2,
                apl2.validasi_apl2
            ')
            ->get()->getResultArray();
    }

    /**
     * Mengambil detail untuk email sertifikasi berdasarkan ID pengajuan.
     * PENYESUAIAN: Join diubah dan kolom validator APL1 diganti dengan validator dari tabel pengajuan.
     */
    public function emailDetailSertifikasi($id_pengajuan)
    {
        return $this->db->table('apl2')
            ->where('pa.id_pengajuan', $id_pengajuan)
            ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = apl2.id_pengajuan', 'left')
            ->join('asesi', 'asesi.id_asesi = pa.id_asesi', 'left')
            ->join('users', 'users.id = asesi.id_user', 'left')
            ->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen', 'left')
            ->join('tuk', 'asesmen.id_tuk=tuk.id_tuk', 'left')
            ->join('users as admin_users', 'admin_users.id=pa.validator_id', 'left') // Mengambil validator dari pengajuan
            ->join('users as asesor_users', 'asesor_users.id = apl2.validator', 'left')
            ->join('skema', 'skema.id_skema=asesmen.id_skema', 'left')
            ->join('set_tanggal', 'set_tanggal.id_tanggal=asesmen.id_tanggal', 'left')
            ->select('
                pa.id_pengajuan,
                skema.nama_skema,
                skema.jenis_skema,
                tuk.nama_tuk,
                tuk.jenis_tuk,
                users.nama_lengkap as nama_siswa,
                asesi.tanda_tangan_asesi,
                pa.created_at,
                set_tanggal.tanggal,
                admin_users.nama_lengkap as validator_pengajuan,
                apl2.id_apl2,
                asesor_users.nama_lengkap as validator_apl2
            ')
            ->get()->getRowArray();
    }

    /**
     * Mengambil data asesor berdasarkan tanda tangan.
     * PENYESUAIAN: memperbaiki kondisi where clause.
     */
    public function getbyttdAsesor($ttd)
    {
        return $this->db->table('apl2')
            ->join('users', 'users.id=apl2.validator', 'left')
            ->where('users.tanda_tangan', $ttd) // Kondisi seharusnya pada tabel users
            ->select('users.tanda_tangan as tanda_tangan_validator, users.nama_lengkap, apl2.created_at')
            ->get()->getRowArray();
    }


    public function getbyId($id)
    {
        return $this->db->table('apl2')
            ->where('id_apl1', $id)
            ->join('apl2_jawaban', 'apl2_jawaban.kode_jawaban_apl2=apl2.kode_jawaban_apl2', 'left')
            ->join('skema', 'skema.id_skema=apl2_jawaban.id_skema', 'left')
            ->join('unit', 'unit.id_unit=apl2_jawaban.id_unit', 'left')
            ->join('elemen', 'elemen.id_elemen=apl2_jawaban.id_elemen', 'left')
            ->join('kuk', 'kuk.id_kuk=apl2_jawaban.id_subelemen', 'left')
            ->select('apl2.id_apl1,apl2_jawaban.kode_jawaban_apl2,apl2_jawaban.bukti_pendukung,apl2_jawaban.tk, skema.nama_skema, unit.id_unit, unit.nama_unit, elemen.id_elemen, elemen.nama_elemen,kuk.id_kuk, kuk.pertanyaan')
            ->Get()->getResultArray();
    }
}
