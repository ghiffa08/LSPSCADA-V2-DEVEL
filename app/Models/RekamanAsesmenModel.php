<?php

namespace App\Models;

use CodeIgniter\Model;

class RekamanAsesmenModel extends Model
{
    protected $table = 'rekaman_asesmen';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_pengajuan', // UBAH: dari id_apl1 ke id_pengajuan
        'rekomendasi',
        'komentar',
        'tindak_lanjut',
        'ttd_asesi',
        'ttd_asesor',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get rekaman with all details including kompetensi
     *
     * @param int $id_rekaman
     * @return array|null
     */
    public function getRekamanWithDetails(int $id_rekaman): ?array
    {
        try {
            // Get main rekaman record
            $rekaman = $this->find($id_rekaman);
            if (!$rekaman) {
                return null;
            }

            // PERBAIKAN: Get pengajuan data menggunakan id_pengajuan
            $pengajuan = $this->db->table('pengajuan_asesmen pa')
                ->select('
                    pa.id_pengajuan,
                    pa.id_asesi,
                    pa.id_skema,
                    a.nik,
                    u.nama_lengkap as nama_asesi,
                    s.nama_skema,
                    s.kode_skema
                ')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'left')
                ->join('users u', 'u.id = a.id_user', 'left')
                ->join('skema s', 's.id_skema = pa.id_skema', 'left')
                ->where('pa.id_pengajuan', $rekaman['id_pengajuan']) // UBAH: dari id_apl1 ke id_pengajuan
                ->get()
                ->getRowArray();

            if (!$pengajuan) {
                log_message('warning', 'Pengajuan not found for id_pengajuan: ' . $rekaman['id_pengajuan']);
                return null;
            }

            // Get kompetensi details
            $kompetensi = $this->db->table('rekaman_asesmen_kompetensi rak')
                ->select('
                    rak.*,
                    u.kode_unit,
                    u.nama_unit
                ')
                ->join('unit u', 'u.id_unit = rak.id_unit', 'left')
                ->where('rak.id_rekaman', $id_rekaman)
                ->orderBy('u.kode_unit', 'ASC')
                ->get()
                ->getResultArray();

            // Merge all data
            $result = array_merge($rekaman, $pengajuan);
            $result['kompetensi'] = $kompetensi;

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error getting rekaman with details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get rekaman by id_pengajuan
     *
     * @param int $id_pengajuan
     * @return array|null
     */
    public function getByIdPengajuan(int $id_pengajuan): ?array
    {
        return $this->where('id_pengajuan', $id_pengajuan)
            ->where('deleted_at', null)
            ->first();
    }

    /**
     * Get all rekaman for asesor
     *
     * @param int $id_asesor
     * @return array
     */
    public function getForAsesor(int $id_asesor): array
    {
        try {
            return $this->db->table('rekaman_asesmen ra')
                ->select('
                    ra.*,
                    pa.id_asesi,
                    u.nama_lengkap as nama_asesi,
                    s.nama_skema,
                    s.kode_skema
                ')
                ->join('pengajuan_asesmen pa', 'pa.id_pengajuan = ra.id_pengajuan', 'left') // UBAH: relasi ke id_pengajuan
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'left')
                ->join('users u', 'u.id = a.id_user', 'left')
                ->join('skema s', 's.id_skema = pa.id_skema', 'left')
                ->join('asesor_asesmen aa', 'aa.id_asesmen = pa.id_asesmen', 'left')
                ->where('aa.id_asesor', $id_asesor)
                ->where('ra.deleted_at', null)
                ->orderBy('ra.created_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error getting rekaman for asesor: ' . $e->getMessage());
            return [];
        }
    }
}
