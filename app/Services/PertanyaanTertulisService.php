<?php

namespace App\Services;

use App\Models\PertanyaanTertulisModel;
use CodeIgniter\Database\BaseConnection;
use Exception;

class PertanyaanTertulisService
{
    protected PertanyaanTertulisModel $ujianModel;
    protected BaseConnection $db;

    public function __construct()
    {
        $this->ujianModel = new PertanyaanTertulisModel();
        $this->db = \Config\Database::connect();
    }

    public function saveUjian(array $data): array
    {
        $this->db->transStart();
        try {
            $masterData = [
                'id_pengajuan'  => $data['id_pengajuan'],
                'id_skema'      => $data['id_skema'],
                'id_asesor'     => !empty($data['id_asesor']) ? $data['id_asesor'] : null, // Pastikan kosong jadi null
                'tanggal_ujian' => $data['tanggal_ujian'] ?? date('Y-m-d'),
                'catatan'       => $data['catatan'] ?? null,
            ];

            $ujian = $this->ujianModel->where('id_pengajuan', $masterData['id_pengajuan'])
                ->where('id_skema', $masterData['id_skema'])
                ->first();

            if ($ujian) {
                $id_ujian = $ujian['id_ujian'];
                $this->ujianModel->update($id_ujian, $masterData);
            } else {
                $id_ujian = $this->ujianModel->insert($masterData, true);
                if (!$id_ujian) {
                    log_message('error', 'Insert ujian gagal: ' . json_encode($masterData));
                    throw new Exception('Gagal membuat sesi ujian.');
                }
            }

            $jawabanModel = new \App\Models\PertanyaanTertulisJawabanModel();
            $jawabanModel->upsertJawaban($id_ujian, $data['jawaban'] ?? []);

            // Hitung jumlah benar setelah upsert
            $benar = 0;
            $jawabanData = $data['jawaban'] ?? [];
            foreach ($jawabanData as $id_soal => $jawaban) {
                $soal = $this->db->table('pertanyaan_tertulis_soal')->where('id_soal', $id_soal)->get()->getRowArray();
                if ($soal['jenis_soal'] == 'PILIHAN_GANDA') {
                    $pilihan = $this->db->table('pertanyaan_tertulis_pilihan')->where('id_pilihan', $jawaban['jawaban_pilihan'])->get()->getRowArray();
                    if ($pilihan && $pilihan['is_benar'] == 'Y') {
                        $benar++;
                    }
                } elseif ($soal['jenis_soal'] == 'BENAR_SALAH') {
                    if ($jawaban['jawaban_benar_salah'] == 'Y') {
                        $benar++;
                    }
                }
            }

            // Update jumlah_benar di tabel pertanyaan_tertulis
            $this->ujianModel->update($id_ujian, ['jumlah_benar' => $benar]);

            $this->db->transComplete();
            return ['success' => true, 'message' => 'Ujian berhasil disimpan.', 'id_ujian' => $id_ujian];
        } catch (Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Save ujian error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
