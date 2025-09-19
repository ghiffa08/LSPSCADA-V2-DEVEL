<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class PMOModel extends Model
{
    use DataTableTrait;

    protected $table            = 'pmo';
    protected $primaryKey       = 'id_pmo';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesor',
        'id_skema',
        'id_apl1',
        'tanggal_observasi',
        'catatan'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['apl1.nama_siswa', 'apl1.nik', 'skema.nama_skema'];

    /**
     * Menerapkan join untuk query DataTable
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder
            ->join('apl1', 'apl1.id_apl1 = pmo.id_apl1', 'left')
            ->join('skema', 'skema.id_skema = pmo.id_skema', 'left')
            ->join('asesor', 'asesor.id_asesor = pmo.id_asesor', 'left')
            ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'left');
    }

    /**
     * Menerapkan field select kustom untuk query DataTable
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'pmo.id_pmo, pmo.tanggal_observasi, 
             apl1.nama_siswa, 
             skema.nama_skema, 
             asesor_user.nama_lengkap as nama_asesor'
        );
    }

    /**
     * Mengambil struktur pertanyaan PMO untuk skema tertentu
     */
    public function getStrukturPmoSkema(int $id_skema): array
    {
        $sql = "
            SELECT 
                s.id_skema, s.nama_skema,
                COALESCE(kk.id_kelompok, 1) as id_kelompok,
                COALESCE(kk.nama_kelompok, 'Kelompok Utama') as nama_kelompok,
                u.id_unit, u.kode_unit, u.nama_unit,
                e.id_elemen, e.nama_elemen, e.kode_elemen,
                k.id_kuk, k.kode_kuk, k.pertanyaan as kriteria_unjuk_kerja,
                pp.id_pertanyaan, pp.pertanyaan, pp.jenis_jawaban,
                pj.id_pilihan, pj.pilihan as pilihan_jawaban
            FROM skema s
            INNER JOIN pmo_pertanyaan pp ON pp.id_skema = s.id_skema
            INNER JOIN unit u ON u.id_unit = pp.id_unit
            INNER JOIN elemen e ON e.id_elemen = pp.id_elemen
            INNER JOIN kuk k ON k.id_kuk = pp.id_kuk
            LEFT JOIN kelompok_unit ku ON ku.id_unit = u.id_unit
            LEFT JOIN kelompok_kerja kk ON kk.id_kelompok = ku.id_kelompok AND kk.id_skema = s.id_skema
            LEFT JOIN pmo_pilihan_jawaban pj ON pj.id_pertanyaan = pp.id_pertanyaan
            WHERE s.id_skema = ? AND pp.aktif = 'Y' AND u.status = 'Y'
            ORDER BY COALESCE(kk.id_kelompok, 1), u.kode_unit, e.id_elemen, k.kode_kuk, pp.urutan, pj.urutan
        ";

        $rawData = $this->db->query($sql, [$id_skema])->getResultArray();
        return $this->transformToHierarchicalStructure($rawData);
    }

    /**
     * Mengubah data flat menjadi struktur hierarkis
     */
    private function transformToHierarchicalStructure(array $rawData): array
    {
        $structure = ['skema' => null, 'kelompok_kerja' => []];
        if (empty($rawData)) return $structure;

        $structure['skema'] = ['id_skema' => $rawData[0]['id_skema'], 'nama_skema' => $rawData[0]['nama_skema']];

        foreach ($rawData as $row) {
            $kelompokId = $row['id_kelompok'];
            $unitId = $row['id_unit'];
            $elemenId = $row['id_elemen'];
            $kukId = $row['id_kuk'];
            $pertanyaanId = $row['id_pertanyaan'];

            if (!isset($structure['kelompok_kerja'][$kelompokId])) {
                $structure['kelompok_kerja'][$kelompokId] = ['id_kelompok' => $kelompokId, 'nama_kelompok' => $row['nama_kelompok'], 'units' => []];
            }
            if (!isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId] = ['id_unit' => $unitId, 'kode_unit' => $row['kode_unit'], 'nama_unit' => $row['nama_unit'], 'elemen' => []];
            }
            if (!isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId] = ['id_elemen' => $elemenId, 'nama_elemen' => $row['nama_elemen'], 'kode_elemen' => $row['kode_elemen'], 'kuk' => []];
            }
            if (!isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][$kukId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][$kukId] = ['id_kuk' => $kukId, 'kode_kuk' => $row['kode_kuk'], 'kriteria_unjuk_kerja' => $row['kriteria_unjuk_kerja'], 'pertanyaan_list' => []];
            }
            if (!isset($structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][$kukId]['pertanyaan_list'][$pertanyaanId])) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][$kukId]['pertanyaan_list'][$pertanyaanId] = ['id_pertanyaan' => $pertanyaanId, 'pertanyaan' => $row['pertanyaan'], 'jenis_jawaban' => $row['jenis_jawaban'], 'pilihan' => []];
            }
            if ($row['jenis_jawaban'] === 'PILIHAN_GANDA' && $row['id_pilihan']) {
                $structure['kelompok_kerja'][$kelompokId]['units'][$unitId]['elemen'][$elemenId]['kuk'][$kukId]['pertanyaan_list'][$pertanyaanId]['pilihan'][] = ['id_pilihan' => $row['id_pilihan'], 'pilihan' => $row['pilihan_jawaban']];
            }
        }

        $structure['kelompok_kerja'] = array_values($structure['kelompok_kerja']);
        foreach ($structure['kelompok_kerja'] as &$kelompok) {
            $kelompok['units'] = array_values($kelompok['units']);
            foreach ($kelompok['units'] as &$unit) {
                $unit['elemen'] = array_values($unit['elemen']);
                foreach ($unit['elemen'] as &$elemen) {
                    $elemen['kuk'] = array_values($elemen['kuk']);
                    foreach ($elemen['kuk'] as &$kuk) {
                        $kuk['pertanyaan_list'] = array_values($kuk['pertanyaan_list']);
                    }
                }
            }
        }

        return $structure;
    }

    /**
     * Mengambil data PMO berdasarkan ID
     */
    public function getPmoById(int $id_pmo): ?array
    {
        return $this->select('pmo.*, apl1.nama_siswa as nama_asesi, skema.nama_skema')
            ->join('apl1', 'apl1.id_apl1 = pmo.id_apl1')
            ->join('skema', 'skema.id_skema = pmo.id_skema')
            ->find($id_pmo);
    }

    /**
     * Mengambil jawaban yang sudah ada untuk sebuah sesi PMO
     */
    public function getExistingJawaban(int $id_pmo): array
    {
        // Mengambil jawaban dan meng-alias kolom agar sesuai dengan yang diharapkan view
        // 'jawaban_ya_tidak' -> 'pencapaian'
        // 'tanggapan' -> 'jawaban_asesor'
        $result = $this->db->table('pmo_jawaban j')
            ->select('j.id_pertanyaan, j.jawaban_ya_tidak as pencapaian, j.tanggapan as jawaban_asesor')
            ->where('id_pmo', $id_pmo)
            ->get()->getResultArray();

        $formatted = [];
        foreach ($result as $row) {
            $formatted[$row['id_pertanyaan']] = $row;
        }
        return $formatted;
    }

    /**
     * Menyimpan data master PMO dan detail jawabannya
     */
    public function savePmoData(array $masterData, array $jawabanData): ?int
    {
        $db = $this->db;
        $db->transStart();

        try {
            $pmo = $this->where('id_apl1', $masterData['id_apl1'])
                ->where('id_skema', $masterData['id_skema'])
                ->first();

            if ($pmo) {
                $id_pmo = $pmo['id_pmo'];
                $this->update($id_pmo, $masterData);
            } else {
                $id_pmo = $this->insert($masterData, true);
            }

            $pmoJawabanModel = new PMOJawabanModel();
            $pmoJawabanModel->upsertJawaban($id_pmo, $jawabanData);

            $db->transComplete();

            return $db->transStatus() ? $id_pmo : null;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error in savePmoData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil semua data PMO yang diperlukan untuk generate PDF.
     */
    public function getPMOWithDetails(int $id_pmo): array
    {
        try {
            // 1. Ambil data master PMO dengan semua join yang diperlukan
            $pmo = $this->db->table('pmo p')
                ->select([
                    'p.*',
                    'apl1.nik as nik_asesi',
                    'apl1.tanda_tangan_asesi as ttd_asesi',
                    'apl1.nama_siswa as nama_asesi',
                    'asesor_user.nama_lengkap as nama_asesor',
                    'asesor_user.tanda_tangan as ttd_asesor',
                    's.nama_skema',
                    's.jenis_skema',
                    's.kode_skema',
                    's.id_skema',
                    'tuk.nama_tuk',
                    'tuk.jenis_tuk'
                ])
                ->join('apl1', 'apl1.id_apl1 = p.id_apl1')
                ->join('asesor', 'asesor.id_asesor = p.id_asesor')
                ->join('users as asesor_user', 'asesor_user.id = asesor.id_user')
                ->join('skema s', 's.id_skema = p.id_skema')
                ->join('asesmen asm', 'asm.id_asesmen = apl1.id_asesmen')
                ->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'left')
                ->where('p.id_pmo', $id_pmo)
                ->get()
                ->getRowArray();

            if (!$pmo) {
                return ['success' => false, 'message' => 'Data PMO tidak ditemukan.'];
            }

            // 2. Ambil struktur pertanyaan
            $struktur = $this->getStrukturPmoSkema($pmo['id_skema']);

            // 3. Ambil jawaban yang sudah ada
            $jawaban_list = $this->getExistingJawaban($id_pmo);

            return [
                'success' => true,
                'data' => [
                    'pmo' => $pmo,
                    'struktur' => $struktur,
                    'jawaban_list' => $jawaban_list
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting PMO for PDF: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengambil data PMO: ' . $e->getMessage()];
        }
    }
}
