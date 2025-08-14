<?php
// app/Models/PMOModel.php

namespace App\Models;

use CodeIgniter\Model;

class PMOModel extends Model
{
    protected $table = 'pmo';
    protected $primaryKey = 'id_pmo';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_pengajuan',
        'id_asesor',
        'id_asesi',
        'tanggal_pmo',
        'status',
        'total_pertanyaan',
        'pertanyaan_terjawab',
        'progress_percentage',
        'keterangan',
        'ttd_asesi',
        'ttd_asesor',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    /**
     * Get PMO with all details including pertanyaan and jawaban
     */
    public function getPMOWithDetails(int $id_pmo): ?array
    {
        try {
            // Get main PMO record
            $pmo = $this->find($id_pmo);
            if (!$pmo) {
                return null;
            }

            // Get pengajuan data with relations
            $pengajuan = $this->db->table('pengajuan_asesmen pa')
                ->select('
                    pa.id_pengajuan,
                    pa.id_asesi,
                    pa.id_asesmen,
                    a.nik,
                    u.nama_lengkap as nama_asesi,
                    u.email as email_asesi,
                    asm.id_skema,
                    asm.tujuan,
                    s.nama_skema,
                    s.kode_skema,
                    s.jenis_skema,
                    tuk.nama_tuk,
                    tuk.jenis_tuk
                ')
                ->join('asesi a', 'a.id_asesi = pa.id_asesi', 'inner')
                ->join('users u', 'u.id = a.id_user', 'inner')
                ->join('asesmen asm', 'asm.id_asesmen = pa.id_asesmen', 'inner')
                ->join('skema s', 's.id_skema = asm.id_skema', 'inner')
                ->join('tuk', 'tuk.id_tuk = asm.id_tuk', 'inner')
                ->where('pa.id_pengajuan', $pmo['id_pengajuan'])
                ->get()
                ->getRowArray();

            if (!$pengajuan) {
                return null;
            }

            // Get asesor info
            $asesorInfo = $this->db->table('asesor')
                ->select('
                    asesor.nomor_registrasi,
                    asesor_user.nama_lengkap as nama_asesor,
                    asesor_user.email as email_asesor
                ')
                ->join('users asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
                ->where('asesor.id_asesor', $pmo['id_asesor'])
                ->get()
                ->getRowArray();

            // Get pertanyaan with jawaban
            $pertanyaan = $this->db->table('pmo_pertanyaan pp')
                ->select('
                    pp.*,
                    u.kode_unit,
                    u.nama_unit,
                    e.nama_elemen,
                    k.kriteria_unjuk_kerja,
                    pj.id_jawaban,
                    pj.jawaban,
                    pj.jawaban_nilai,
                    pj.is_benar,
                    pj.skor,
                    pj.tanggapan_asesor,
                    pj.catatan as catatan_jawaban
                ')
                ->join('unit u', 'u.id_unit = pp.id_unit', 'inner')
                ->join('elemen e', 'e.id_elemen = pp.id_elemen', 'left')
                ->join('kuk k', 'k.id_kuk = pp.id_kuk', 'left')
                ->join('pmo_jawaban pj', 'pj.id_pertanyaan = pp.id_pertanyaan', 'left')
                ->where('pp.id_pmo', $id_pmo)
                ->orderBy('u.kode_unit', 'ASC')
                ->orderBy('pp.urutan', 'ASC')
                ->get()
                ->getResultArray();

            // Merge all data
            $result = array_merge($pmo, $pengajuan, $asesorInfo);
            $result['pertanyaan'] = $pertanyaan;

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error getting PMO with details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get PMO by pengajuan
     */
    public function getByPengajuan(string $id_pengajuan): ?array
    {
        return $this->where('id_pengajuan', $id_pengajuan)
            ->where('deleted_at', null)
            ->first();
    }

    /**
     * Update progress PMO
     */
    public function updateProgress(int $id_pmo): bool
    {
        try {
            // Calculate progress
            $stats = $this->db->table('pmo_pertanyaan pp')
                ->select('
                    COUNT(*) as total_pertanyaan,
                    COUNT(pj.id_jawaban) as pertanyaan_terjawab
                ')
                ->join('pmo_jawaban pj', 'pj.id_pertanyaan = pp.id_pertanyaan', 'left')
                ->where('pp.id_pmo', $id_pmo)
                ->get()
                ->getRowArray();

            $total = $stats['total_pertanyaan'] ?? 0;
            $terjawab = $stats['pertanyaan_terjawab'] ?? 0;
            $progress = $total > 0 ? ($terjawab / $total) * 100 : 0;

            // Determine status
            $status = 'draft';
            if ($progress > 0 && $progress < 100) {
                $status = 'in_progress';
            } elseif ($progress >= 100) {
                $status = 'completed';
            }

            // Update PMO
            return $this->update($id_pmo, [
                'total_pertanyaan' => $total,
                'pertanyaan_terjawab' => $terjawab,
                'progress_percentage' => round($progress, 2),
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error updating PMO progress: ' . $e->getMessage());
            return false;
        }
    }
}
