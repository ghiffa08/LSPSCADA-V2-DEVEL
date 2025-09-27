<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class PertanyaanTertulisModel extends Model
{
    use DataTableTrait;

    protected $table            = 'pertanyaan_tertulis';
    protected $primaryKey       = 'id_ujian';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_pengajuan',
        'id_skema',
        'id_asesor',
        'tanggal_ujian',
        'catatan',
        'jumlah_benar'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Diubah untuk mencari berdasarkan nama lengkap asesi (dari tabel users) dan nik (dari tabel asesi)
    protected $dataTableSearchFields = ['user_asesi.nama_lengkap', 'asesi.nik', 'skema.nama_skema'];

    protected function applyDataTableJoins($builder)
    {
        return $builder
            // Join ke pengajuan untuk mendapatkan data asesi
            ->join('pengajuan_asesmen', 'pengajuan_asesmen.id_pengajuan = pertanyaan_tertulis.id_pengajuan', 'inner')
            ->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi', 'inner')
            ->join('users as user_asesi', 'user_asesi.id = asesi.id_user', 'inner')
            // Join ke users untuk mendapatkan nama asesor
            ->join('users as asesor_user', 'asesor_user.id = pertanyaan_tertulis.id_asesor', 'left')
            ->join('skema', 'skema.id_skema = pertanyaan_tertulis.id_skema', 'inner');
    }

    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'pertanyaan_tertulis.*, 
            asesor_user.nama_lengkap AS nama_asesor, 
            user_asesi.nama_lengkap AS nama_asesi,
            asesi.nik,
            skema.nama_skema'
        );
    }

    public function getStrukturUjianSkema(int $id_skema): array
    {
        $soalModel = new PertanyaanTertulisSoalModel();
        $pilihanModel = new PertanyaanTertulisPilihanModel();

        $soalList = $soalModel->getBySkema($id_skema);
        $soalIds = array_column($soalList, 'id_soal');

        if (empty($soalIds)) {
            return ['soal_list' => []];
        }

        $pilihanList = $pilihanModel->whereIn('id_soal', $soalIds)->orderBy('urutan', 'ASC')->findAll();
        $pilihanBySoal = [];
        foreach ($pilihanList as $pilihan) {
            $pilihanBySoal[$pilihan['id_soal']][] = $pilihan;
        }

        foreach ($soalList as &$soal) {
            if (isset($pilihanBySoal[$soal['id_soal']])) {
                $soal['pilihan'] = $pilihanBySoal[$soal['id_soal']];
            }
        }

        return ['soal_list' => $soalList];
    }

    public function getExistingJawaban(int $id_ujian): array
    {
        $result = $this->db->table('pertanyaan_tertulis_jawaban')
            ->where('id_ujian', $id_ujian)
            ->get()->getResultArray();

        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['id_soal']] = $row;
        }
        return $formatted;
    }

    /**
     * [FUNGSI DIPERBARUI] Mengambil daftar ujian tertulis untuk user tertentu.
     * Kini menampilkan pengajuan yang sudah diterima (untuk dikerjakan) dan selesai (untuk riwayat).
     *
     * @param int    $userId ID dari tabel users
     * @param string $filter Tipe urutan ('terbaru' atau 'terlama')
     * @return array
     */
    public function getByUserId(int $userId, string $filter = 'terbaru'): array
    {
        // Menggunakan alias untuk mempermudah pembacaan query
        $builder = $this->db->table('pengajuan_asesmen pa');

        $builder->select('
            pa.id_pengajuan,
            skema.nama_skema,
            pt.id_ujian,
            pt.tanggal_ujian,
            pt.updated_at,
            pa.created_at,
            pa.status_pengajuan
        ');

        $builder->join('asesi', 'asesi.id_asesi = pa.id_asesi');
        $builder->join('asesmen', 'asesmen.id_asesmen = pa.id_asesmen');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema');
        // Gunakan LEFT JOIN agar pengajuan yang diterima tapi ujiannya belum dimulai tetap muncul
        $builder->join('pertanyaan_tertulis pt', 'pt.id_pengajuan = pa.id_pengajuan', 'left');

        // Filter untuk user yang sedang login
        $builder->where('asesi.id_user', $userId);
        // Filter untuk pengajuan yang statusnya 'diterima' (untuk dikerjakan) atau 'selesai' (untuk riwayat)
        $builder->whereIn('pa.status_pengajuan', ['diterima', 'selesai']);

        // Logika untuk filter pengurutan data
        if ($filter === 'terlama') {
            $builder->orderBy('pa.created_at', 'ASC');
        } else {
            // Default pengurutan adalah 'terbaru'
            $builder->orderBy('pa.created_at', 'DESC');
        }

        return $builder->get()->getResultArray();
    }
}
