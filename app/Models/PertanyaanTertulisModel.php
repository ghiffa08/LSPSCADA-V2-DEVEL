<?php

// File: app/Models/PertanyaanTertulisModel.php

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
        'id_asesor',
        'id_skema',
        'id_apl1',
        'tanggal_ujian',
        'catatan'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $dataTableSearchFields = ['apl1.nama_siswa', 'apl1.nik', 'skema.nama_skema'];

    protected function applyDataTableJoins($builder)
    {
        return $builder->join('apl1', 'apl1.id_apl1 = pertanyaan_tertulis.id_apl1', 'inner')
            ->join('asesor', 'asesor.id_asesor = pertanyaan_tertulis.id_asesor', 'inner')
            ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
            ->join('skema', 'skema.id_skema = pertanyaan_tertulis.id_skema', 'inner');
    }

    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'pertanyaan_tertulis.*, 
            asesor_user.nama_lengkap AS nama_asesor, 
            apl1.nama_siswa AS nama_asesi,
            apl1.nik,
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
}

