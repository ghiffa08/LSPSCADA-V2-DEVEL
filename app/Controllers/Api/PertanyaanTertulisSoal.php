<?php

namespace App\Controllers\Api;

use Config\Database;
use App\Models\PertanyaanTertulisSoalModel;
use App\Models\PertanyaanTertulisPilihanModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use Exception;

class PertanyaanTertulisSoal extends DataTableController
{
    protected PertanyaanTertulisPilihanModel $pilihanModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PertanyaanTertulisSoalModel();
        $this->pilihanModel = new PertanyaanTertulisPilihanModel();

        $this->columnMap = [
            0 => null,
            1 => 'pertanyaan_tertulis_soal.id_soal',
            2 => 'skema.nama_skema',
            3 => 'pertanyaan_tertulis_soal.soal',
            4 => 'pertanyaan_tertulis_soal.jenis_soal',
            5 => null
        ];
    }

    /**
     * Simpan atau perbarui data soal dan pilihan jawabannya.
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $data = $this->request->getPost();

        $soalData = [
            'id_soal' => $data['id_soal'] ?? null,
            'id_skema' => $data['id_skema'],
            'soal' => $data['soal'],
            'jenis_soal' => $data['jenis_soal'],
            'urutan' => $data['urutan'] ?? 0,
            'aktif' => $data['aktif'] ?? 'Y'
        ];

        $db = Database::connect();
        $db->transStart();

        try {
            // Simpan data soal utama
            $result = $this->dataService->save(PertanyaanTertulisSoalModel::class, $soalData, 'id_soal');
            if (!$result['status']) {
                throw new Exception($result['message'] ?? 'Gagal menyimpan data soal.');
            }

            $id_soal = $result['id'];

            // Hapus pilihan jawaban lama untuk soal ini
            $this->pilihanModel->where('id_soal', $id_soal)->delete();

            // Proses penyimpanan pilihan jawaban jika ada
            if ($soalData['jenis_soal'] === 'PILIHAN_GANDA') {
                $pilihan = $data['pilihan'] ?? [];
                $isBenarIndex = $data['is_benar'] ?? -1;
                $pilihanToInsert = [];
                foreach ($pilihan as $index => $teksPilihan) {
                    if (!empty(trim($teksPilihan))) {
                        $pilihanToInsert[] = [
                            'id_soal' => $id_soal,
                            'pilihan' => $teksPilihan,
                            'is_benar' => ($index == $isBenarIndex) ? 'Y' : 'N',
                            'urutan' => $index + 1
                        ];
                    }
                }
                if (!empty($pilihanToInsert)) {
                    $this->pilihanModel->insertBatch($pilihanToInsert);
                }
            } elseif ($soalData['jenis_soal'] === 'BENAR_SALAH') {
                $jawabanBenar = $data['jawaban_benar_salah'] ?? 'Y';
                $pilihanToInsert = [
                    ['id_soal' => $id_soal, 'pilihan' => 'Benar', 'is_benar' => ($jawabanBenar === 'Y' ? 'Y' : 'N'), 'urutan' => 1],
                    ['id_soal' => $id_soal, 'pilihan' => 'Salah', 'is_benar' => ($jawabanBenar === 'N' ? 'Y' : 'N'), 'urutan' => 2]
                ];
                $this->pilihanModel->insertBatch($pilihanToInsert);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new Exception('Transaksi database gagal.');
            }

            return $this->dataService->response($result, $result['code']);
        } catch (Exception $e) {
            $db->transRollback();
            return $this->dataService->response(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Ambil data soal berdasarkan ID, termasuk pilihan jawabannya.
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        $soal = $this->model->find($id);
        if (!$soal) {
            return $this->dataService->response(['status' => false, 'message' => 'Soal tidak ditemukan'], 404);
        }

        // Jika soal memiliki pilihan, ambil juga datanya
        if (in_array($soal['jenis_soal'], ['PILIHAN_GANDA', 'BENAR_SALAH'])) {
            $soal['pilihan'] = $this->pilihanModel
                ->where('id_soal', $id)
                ->orderBy('urutan', 'ASC')
                ->findAll();
        }

        return $this->dataService->response(['status' => true, 'data' => $soal]);
    }

    /**
     * Hapus data soal.
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(404);
        }

        // ON DELETE CASCADE di database akan otomatis menghapus pilihan jawaban terkait
        $deleted = $this->model->delete($id);

        if ($deleted) {
            return $this->dataService->response(['status' => true, 'message' => 'Soal berhasil dihapus']);
        } else {
            return $this->dataService->response(['status' => false, 'message' => 'Gagal menghapus soal'], 400);
        }
    }
}
