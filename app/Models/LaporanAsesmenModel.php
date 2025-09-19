<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanAsesmenModel extends Model
{
    protected $table = 'rekaman_asesmen';
    protected $primaryKey = 'id';
    protected $returnType = 'array';



    /**
     * Get semua data asesi untuk laporan asesmen berdasarkan asesor
     * 
     * @param int $id_asesor ID Asesor
     * @param array $filters Optional additional filters
     * @return array
     */
    public function getAllLaporanData($id_asesor, $filters = [])
    {
        if (!$id_asesor) {
            return [];
        }

        $builder = $this->db->table('rekaman_asesmen ra');

        $builder->select([
            'ra.id as id_rekaman',
            'ra.rekomendasi',
            'ra.tanggal_rekaman',
            'ra.komentar',
            'ra.tindak_lanjut',
            'ra.id_apl1',
            'ra.id_asesor',
            // Data Asesi dari APL1
            'apl1.nama_siswa as nama_asesi',
            'apl1.nik',
            'apl1.tempat_lahir',
            'apl1.tanggal_lahir',
            'apl1.email',
            // Data Skema
            's.nama_skema',
            's.kode_skema',
            // Data TUK
            't.nama_tuk',
            't.jenis_tuk',
            // Data Asesor
            'u.nama_lengkap as nama_asesor',
            'asesor.nomor_registrasi as nomor_reg_asesor',
            // Data Tanggal Asesmen
            'st.tanggal as tanggal_asesmen',
            'asm.tujuan as tujuan_asesmen'
        ])
            ->join('apl1', 'apl1.id_apl1 = ra.id_apl1')
            ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen')
            ->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal')
            ->join('skema s', 's.id_skema = asm.id_skema')
            ->join('tuk t', 't.id_tuk = asm.id_tuk')
            ->join('asesor', 'asesor.id_asesor = ra.id_asesor')
            ->join('users u', 'u.id = asesor.id_user')
            ->where('ra.deleted_at IS NULL')
            ->where('ra.id_asesor', $id_asesor);

        // Apply additional filters if any
        if (!empty($filters['id_skema'])) {
            $builder->where('asm.id_skema', $filters['id_skema']);
        }
        if (!empty($filters['tanggal_dari'])) {
            $builder->where('DATE(ra.tanggal_rekaman) >=', $filters['tanggal_dari']);
        }
        if (!empty($filters['tanggal_sampai'])) {
            $builder->where('DATE(ra.tanggal_rekaman) <=', $filters['tanggal_sampai']);
        }

        $builder->groupBy([
            'ra.id',
            'apl1.nama_siswa',
            'apl1.nik',
            'apl1.tempat_lahir',
            'apl1.tanggal_lahir',
            'apl1.email',
            's.nama_skema',
            's.kode_skema',
            't.nama_tuk',
            't.jenis_tuk',
            'u.nama_lengkap',
            'asesor.nomor_registrasi',
            'st.tanggal',
            'asm.tujuan'
        ]);

        $builder->orderBy('ra.id', 'DESC')
            ->orderBy('apl1.nama_siswa', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get general info untuk header laporan
     */
    public function getGeneralInfo($filters = [])
    {
        $builder = $this->db->table('rekaman_asesmen ra');

        $builder->select([
            's.kode_skema',
            's.nama_skema',
            's.jenis_skema',
            't.nama_tuk',
            't.jenis_tuk',
            'u.nama_lengkap as nama_asesor',
            'asesor.nomor_registrasi as nomor_reg_asesor',
            'ra.tanggal_rekaman',
            'st.tanggal as tanggal_asesmen',
            'asm.tujuan as tujuan_asesmen',
            'set_tanggal.tanggal as tanggal_asesmen'

        ])
            ->join('apl1', 'apl1.id_apl1 = ra.id_apl1')
            ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen')
            ->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal')
            ->join('skema s', 's.id_skema = asm.id_skema')
            ->join('tuk t', 't.id_tuk = asm.id_tuk')
            ->join('asesor', 'asesor.id_asesor = ra.id_asesor')
            ->join('users u', 'u.id = asesor.id_user')
            ->join('set_tanggal', 'set_tanggal.id_tanggal = asm.id_tanggal')
            ->where('ra.deleted_at IS NULL');

        // Apply basic filters only
        if (!empty($filters['id_asesor'])) {
            $builder->where('ra.id_asesor', $filters['id_asesor']);
        }
        if (!empty($filters['id_skema'])) {
            $builder->where('asm.id_skema', $filters['id_skema']);
        }

        return $builder->orderBy('ra.tanggal_rekaman', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();
    }

    public function getLaporanList($filters = [])
    {
        $builder = $this->db->table('rekaman_asesmen ra');
        $builder->select('
        MIN(ra.id) as id,
        ra.tanggal_rekaman,
        s.nama_skema,
        s.kode_skema,
        s.id_skema,
        a.nama_lengkap as nama_asesor,
        a.id as id_user,
        MIN(asesor.id_asesor) as id_asesor,
        COUNT(DISTINCT ra.id_apl1) as jumlah_asesi,
        SUM(CASE WHEN ra.rekomendasi = "kompeten" THEN 1 ELSE 0 END) as jumlah_kompeten,
        SUM(CASE WHEN ra.rekomendasi = "belum_kompeten" THEN 1 ELSE 0 END) as jumlah_belum_kompeten
    ');

        // Join tables with proper conditions
        $builder->join('apl1', 'apl1.id_apl1 = ra.id_apl1', 'left')
            ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen', 'left')
            ->join('skema s', 's.id_skema = asm.id_skema', 'left')
            ->join('asesor', 'asesor.id_asesor = ra.id_asesor', 'left')
            ->join('users a', 'a.id = asesor.id_user', 'left')
            ->where('ra.deleted_at IS NULL');

        // Apply filters
        if (!empty($filters['id_asesor'])) {
            $builder->where('ra.id_asesor', $filters['id_asesor']);
        }
        if (!empty($filters['id_skema'])) {
            $builder->where('s.id_skema', $filters['id_skema']);
        }
        if (!empty($filters['tanggal_dari'])) {
            $builder->where('DATE(ra.tanggal_rekaman) >=', $filters['tanggal_dari']);
        }
        if (!empty($filters['tanggal_sampai'])) {
            $builder->where('DATE(ra.tanggal_rekaman) <=', $filters['tanggal_sampai']);
        }

        // Group by all non-aggregated columns
        $builder->groupBy([
            'ra.tanggal_rekaman',
            's.id_skema',
            's.nama_skema',
            's.kode_skema',
            'a.id',
            'a.nama_lengkap',
            'asesor.id_asesor'
        ]);

        // Order by latest date first
        $builder->orderBy('ra.tanggal_rekaman', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get statistik laporan
     */
    public function getLaporanStatistik($filters = [])
    {
        $builder = $this->db->table('rekaman_asesmen ra');

        $builder->select([
            'COUNT(*) as total_asesi',
            'SUM(CASE WHEN ra.rekomendasi = "kompeten" THEN 1 ELSE 0 END) as total_kompeten',
            'SUM(CASE WHEN ra.rekomendasi = "belum_kompeten" THEN 1 ELSE 0 END) as total_belum_kompeten',
            'ROUND((SUM(CASE WHEN ra.rekomendasi = "kompeten" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as persentase_kompeten'
        ])
            ->join('apl1', 'apl1.id_apl1 = ra.id_apl1')
            ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen')
            ->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal')
            ->where('ra.deleted_at IS NULL');

        // Apply filters
        $this->applyFilters($builder, $filters);

        return $builder->get()->getRowArray();
    }

    /**
     * Get list asesor untuk filter
     */
    public function getAsesorList()
    {
        return $this->db->table('asesor a')
            ->select('a.id_asesor, u.nama_lengkap as nama_asesor, a.nomor_registrasi')
            ->join('users u', 'u.id = a.id_user')
            // ->where('a.deleted_at IS NULL')
            ->orderBy('u.nama_lengkap')
            ->get()
            ->getResultArray();
    }

    /**
     * Get list skema untuk filter
     */
    public function getSkemaList()
    {
        return $this->db->table('skema')
            ->select('id_skema, nama_skema, kode_skema')
            // ->where('deleted_at IS NULL')
            ->orderBy('nama_skema')
            ->get()
            ->getResultArray();
    }

    /**
     * Helper method untuk apply filters (DRY principle)
     */
    private function applyFilters($builder, $filters)
    {
        if (!empty($filters['id_asesor'])) {
            $builder->where('ra.id_asesor', $filters['id_asesor']);
        }
        if (!empty($filters['id_skema'])) {
            $builder->where('asm.id_skema', $filters['id_skema']);
        }
        if (!empty($filters['rekomendasi'])) {
            $builder->where('ra.rekomendasi', $filters['rekomendasi']);
        }
        if (!empty($filters['tanggal_dari'])) {
            $builder->where('DATE(ra.tanggal_rekaman) >=', $filters['tanggal_dari']);
        }
        if (!empty($filters['tanggal_sampai'])) {
            $builder->where('DATE(ra.tanggal_rekaman) <=', $filters['tanggal_sampai']);
        }
        if (!empty($filters['tanggal_asesmen_dari'])) {
            $builder->where('DATE(st.tanggal) >=', $filters['tanggal_asesmen_dari']);
        }
        if (!empty($filters['tanggal_asesmen_sampai'])) {
            $builder->where('DATE(st.tanggal) <=', $filters['tanggal_asesmen_sampai']);
        }
    }

    /**
     * Method KHUSUS untuk ambil asesi kompeten saja
     */
    public function getAsesiKompeten($filters = [])
    {
        $builder = $this->db->table('rekaman_asesmen ra');

        $builder->select([
            'ra.id as id_rekaman',
            'ra.rekomendasi',
            'ra.tanggal_rekaman',
            'apl1.nama_siswa as nama_asesi',
            'apl1.nik',
            'apl1.email',
            's.nama_skema',
            's.kode_skema',
            'u.nama_lengkap as nama_asesor',
            'st.tanggal as tanggal_asesmen'
        ])
            ->join('apl1', 'apl1.id_apl1 = ra.id_apl1')
            ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen')
            ->join('skema s', 's.id_skema = asm.id_skema')
            ->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal')
            ->join('asesor', 'asesor.id_asesor = ra.id_asesor')
            ->join('users u', 'u.id = asesor.id_user')
            ->where('ra.rekomendasi', 'kompeten') // HANYA yang kompeten
            ->where('ra.deleted_at IS NULL');

        // Apply filter skema jika ada
        if (!empty($filters['id_skema'])) {
            $builder->where('asm.id_skema', $filters['id_skema']);
        }

        $result = $builder->orderBy('ra.id', 'DESC')->get()->getResultArray();

        log_message('info', "Asesi kompeten found: " . count($result));
        return $result;
    }

    /**
     * Method KHUSUS untuk ambil asesi belum kompeten saja
     */
    public function getAsesiBelumKompeten($filters = [])
    {
        $builder = $this->db->table('rekaman_asesmen ra');

        $builder->select([
            'ra.id as id_rekaman',
            'ra.rekomendasi',
            'ra.tanggal_rekaman',
            'apl1.nama_siswa as nama_asesi',
            'apl1.nik',
            'apl1.email',
            's.nama_skema',
            's.kode_skema',
            'u.nama_lengkap as nama_asesor',
            'st.tanggal as tanggal_asesmen'
        ])
            ->join('apl1', 'apl1.id_apl1 = ra.id_apl1')
            ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen')
            ->join('skema s', 's.id_skema = asm.id_skema')
            ->join('set_tanggal st', 'st.id_tanggal = asm.id_tanggal')
            ->join('asesor', 'asesor.id_asesor = ra.id_asesor')
            ->join('users u', 'u.id = asesor.id_user')
            ->where('ra.rekomendasi', 'belum_kompeten') // HANYA yang belum kompeten
            ->where('ra.deleted_at IS NULL');

        // Apply filter skema jika ada
        if (!empty($filters['id_skema'])) {
            $builder->where('asm.id_skema', $filters['id_skema']);
        }

        $result = $builder->orderBy('ra.id', 'DESC')->get()->getResultArray();

        log_message('info', "Asesi belum kompeten found: " . count($result));
        return $result;
    }
}
