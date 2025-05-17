<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class KelompokKerjaModel extends Model
{
    use DataTableTrait;

    protected $table = 'kelompok_kerja';
    protected $primaryKey = 'id_kelompok';
    protected $allowedFields = ['nama_kelompok', 'id_skema', 'created_at', 'updated_at'];

    // Fields that should be searched when using DataTable
    protected $dataTableSearchFields = ['kelompok_kerja.nama_kelompok', 'skema.nama_skema'];

    /**
     * Apply joins for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder->join('skema', 'skema.id_skema = kelompok_kerja.id_skema', 'left')
            ->join(
                '(SELECT id_kelompok, COUNT(*) as jumlah_unit FROM kelompok_unit GROUP BY id_kelompok) as unit_count',
                'unit_count.id_kelompok = kelompok_kerja.id_kelompok',
                'left'
            );
    }

    /**
     * Apply custom select fields for DataTable query
     *
     * @param object $builder Query builder instance
     * @return object
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select('kelompok_kerja.*, skema.nama_skema, COALESCE(unit_count.jumlah_unit, 0) as jumlah_unit');
    }

    /**
     * Transform DataTable results if needed
     *
     * @param array $data Result data
     * @return array
     */
    protected function transformDataTableResults($data)
    {
        // You can transform data here if needed
        // For example, format dates, calculate values, etc.
        return $data;
    }

    /**
     * Get kelompok kerja with skema details
     *
     * @param int $id
     * @return array|null
     */
    public function getKelompokWithSkema($id)
    {
        $builder = $this->db->table($this->table . ' kk');
        $builder->select('kk.*, s.nama_skema');
        $builder->join('skema s', 's.id_skema = kk.id_skema', 'left');
        $builder->where('kk.id_kelompok', $id);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getAllKelompokWithSkemaAndUnits()
    {
        $rows = $this->db->table('kelompok_kerja kk')
            ->select('
            kk.id_kelompok, kk.nama_kelompok,
            s.id_skema, s.kode_skema, s.nama_skema,
            u.id_unit, u.kode_unit, u.nama_unit
        ')
            ->join('skema s', 's.id_skema = kk.id_skema')
            ->join('kelompok_unit ku', 'ku.id_kelompok = kk.id_kelompok')
            ->join('unit u', 'u.id_unit = ku.id_unit')
            ->orderBy('kk.id_kelompok', 'ASC')
            ->get()
            ->getResult();

        // Struktur hasil
        $result = [];

        foreach ($rows as $row) {
            $id = $row->id_kelompok;

            // Jika kelompok belum disimpan, buat entry baru
            if (!isset($result[$id])) {
                $result[$id] = [
                    'id_kelompok' => $row->id_kelompok,
                    'nama_kelompok' => $row->nama_kelompok,
                    'id_skema' => $row->id_skema,
                    'kode_skema' => $row->kode_skema,
                    'nama_skema' => $row->nama_skema,
                    'units' => []
                ];
            }

            // Tambahkan unit ke array
            $result[$id]['units'][] = [
                'id_unit' => $row->id_unit,
                'kode_unit' => $row->kode_unit,
                'nama_unit' => $row->nama_unit
            ];
        }

        // Kembalikan sebagai array numerik
        return array_values($result);
    }


    public function getAllKelompokWithUnits()
    {
        return $this->db->table('kelompok_unit ku')
            ->select('kk.id_kelompok, kk.nama_kelompok, u.id_unit, u.kode_unit, u.nama_unit')
            ->join('kelompok_kerja kk', 'kk.id_kelompok = ku.id_kelompok')
            ->join('unit u', 'u.id_unit = ku.id_unit')
            ->orderBy('kk.id_kelompok', 'ASC')
            ->get()
            ->getResult();
    }


    public function getUnitsByKelompok($id_kelompok)
    {
        return $this->db->table('kelompok_unit ku')
            ->select('u.*')
            ->join('unit u', 'u.id_unit = ku.id_unit')
            ->where('ku.id_kelompok', $id_kelompok)
            ->get()->getResult();
    }
}
