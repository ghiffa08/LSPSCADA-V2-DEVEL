<?php

namespace App\Models;

use App\Traits\DataTableTrait;
use CodeIgniter\Model;

class FeedbackAsesiModel extends Model
{
    use DataTableTrait;

    protected $table            = 'feedback_asesi';
    protected $primaryKey       = 'id_feedback';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesor',
        'id_skema',
        'id_asesi',
        'id_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan_lain'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $dataTableSearchFields = ['asesor_user.nama_lengkap', 'asesi_user.nama_lengkap', 'skema.nama_skema'];

    /**
     * Menerapkan join untuk query DataTable dengan struktur tabel baru
     */
    protected function applyDataTableJoins($builder)
    {
        return $builder
            // Join untuk Asesi
            ->join('asesi', 'asesi.id_asesi = feedback_asesi.id_asesi', 'inner')
            ->join('users as asesi_user', 'asesi_user.id = asesi.id_user', 'inner')
            // Join untuk Asesor
            ->join('asesor', 'asesor.id_asesor = feedback_asesi.id_asesor', 'inner')
            ->join('users as asesor_user', 'asesor_user.id = asesor.id_user', 'inner')
            // Join untuk Skema
            ->join('skema', 'skema.id_skema = feedback_asesi.id_skema', 'inner');
    }

    /**
     * Menerapkan select field kustom untuk query DataTable
     */
    protected function applyDataTableSelects($builder)
    {
        return $builder->select(
            'feedback_asesi.*, 
            asesor_user.nama_lengkap AS nama_asesor, 
            asesi_user.nama_lengkap AS nama_asesi,
            skema.nama_skema'
        );
    }

    /**
     * Mengambil semua komponen/pertanyaan feedback yang aktif (Tidak ada perubahan, sudah baik)
     */
    public function getKomponenFeedback(): array
    {
        return $this->db->table('komponen_feedback')
            ->where('deleted_at', null)
            ->orderBy('urutan', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Mengambil data feedback yang sudah ada berdasarkan id_feedback (Tidak ada perubahan, sudah baik)
     */
    public function getExistingFeedback(int $id_feedback): array
    {
        $result = $this->db->table('detail_feedback_asesi')
            ->select('id_komponen, jawaban, komentar')
            ->where('id_feedback', $id_feedback)
            ->get()
            ->getResultArray();

        $formatted = [];
        foreach ($result as $row) {
            // PERBAIKAN: Konversi jawaban dari tinyint(0/1) ke char('T'/'Y') jika perlu
            // Anggap saja di DB disimpan 'Y' dan 'T' dalam bentuk char/varchar
            $formatted[$row['id_komponen']] = [
                'jawaban'  => $row['jawaban'] === '1' ? 'Y' : ($row['jawaban'] === '0' ? 'T' : $row['jawaban']),
                'komentar' => $row['komentar']
            ];
        }
        return $formatted;
    }

    /**
     * Menyimpan data feedback (master & detail) dalam satu transaksi.
     * Metode ini sekarang menggunakan id_pengajuan sebagai kunci untuk UPSERT.
     *
     * @param array $masterData Data untuk tabel feedback_asesi
     * @param array $detailData Data untuk tabel detail_feedback_asesi
     * @return int|bool ID feedback yang baru dibuat/diupdate, atau false jika gagal.
     */
    public function saveFeedbackData(array $masterData, array $detailData)
    {
        $db = $this->db;
        $db->transStart();

        try {
            // PERBAIKAN UTAMA: Gunakan id_pengajuan untuk mencari data yang sudah ada
            if (empty($masterData['id_pengajuan'])) {
                throw new \Exception("ID Pengajuan wajib diisi.");
            }

            $existing = $this->where('id_pengajuan', $masterData['id_pengajuan'])->first();

            $id_feedback = null;
            if ($existing) {
                // Jika sudah ada, update data master
                $id_feedback = $existing['id_feedback'];
                $this->update($id_feedback, $masterData);
            } else {
                // Jika belum ada, buat record baru
                if ($this->save($masterData) === false) {
                    $errors = $this->errors();
                    throw new \Exception("Gagal menyimpan data master: " . implode(', ', $errors));
                }
                $id_feedback = $this->getInsertID();
            }

            if (!$id_feedback) {
                throw new \Exception("Gagal mendapatkan ID Feedback setelah operasi save.");
            }

            // Hapus detail lama untuk id_feedback ini
            $db->table('detail_feedback_asesi')->where('id_feedback', $id_feedback)->delete();

            // Sisipkan detail baru secara batch
            $batchData = [];
            if (!empty($detailData['jawaban']) && is_array($detailData['jawaban'])) {
                foreach ($detailData['jawaban'] as $id_komponen => $jawaban) {
                    $batchData[] = [
                        'id_feedback' => $id_feedback,
                        'id_komponen' => $id_komponen,
                        'jawaban'     => $jawaban, // Jawaban harus 'Y' atau 'T'
                        'komentar'    => $detailData['komentar'][$id_komponen] ?? null,
                    ];
                }
            }

            if (!empty($batchData)) {
                $db->table('detail_feedback_asesi')->insertBatch($batchData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', 'Transaksi penyimpanan feedback gagal.');
                return false;
            }

            return $id_feedback;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error di saveFeedbackData: ' . $e->getMessage());
            throw $e; // Lanjutkan melempar exception ke controller
        }
    }
}
