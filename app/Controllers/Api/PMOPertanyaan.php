<?php

namespace App\Controllers\Api;

use Config\Database;
use Config\Services;
use App\Models\PMOPertanyaanModel;
use App\Models\PMOPilihanJawabanModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use Exception;

class PMOPertanyaan extends DataTableController
{

    protected $pmoPertanyaanModel;
    protected $pmoPilihanJawabanModel;

    public function __construct()
    {
        parent::__construct();
        $this->pmoPertanyaanModel = new PMOPertanyaanModel();
        $this->pmoPilihanJawabanModel =  new PMOPilihanJawabanModel();

        $this->model = $this->pmoPertanyaanModel;

        // Sesuaikan pemetaan kolom untuk sorting DataTable
        $this->columnMap = [
            0 => null, // Kolom indeks tanpa sorting
            1 => 'pmo_pertanyaan.id_pertanyaan',
            2 => 'skema.nama_skema',
            3 => 'pmo_pertanyaan.pertanyaan',
            4 => 'pmo_pertanyaan.jenis_jawaban',
            5 => 'pmo_pertanyaan.aktif',
            6 => null // Kolom aksi tanpa sorting
        ];
    }

    /**
     * Simpan atau perbarui data pertanyaan PMO
     */
    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $modelName = PmoPertanyaanModel::class;
        $data = $this->request->getPost();
        $pilihanJawaban = $this->request->getPost('pilihan');

        $formattedData = [
            'id_pertanyaan' => $data['id_pertanyaan'] ?? null,
            'id_skema' => $data['id_skema'],
            'id_unit' => $data['id_unit'],
            'id_elemen' => $data['id_elemen'],
            'id_kuk' => $data['id_kuk'],
            'pertanyaan' => $data['pertanyaan'] ?? null,
            'jenis_jawaban' => $data['jenis_jawaban'],
            'urutan' => $data['urutan'] ?? 0,
            'aktif' => $data['aktif'] ?? 'Y'
        ];

        $db = Database::connect();
        $db->transStart();

        try {
            $result = $this->dataService->save($modelName, $formattedData, 'id_pertanyaan');

            if ($result['status']) {
                $pertanyaanId = $result['id'];

                // Hapus pilihan lama terlebih dahulu untuk memastikan konsistensi
                $this->pmoPilihanJawabanModel->where('id_pertanyaan', $pertanyaanId)->delete();

                // Jika jenis jawaban adalah Pilihan Ganda dan ada pilihan yang dikirim
                if ($formattedData['jenis_jawaban'] === 'PILIHAN_GANDA' && !empty($pilihanJawaban)) {
                    $pilihanToInsert = [];
                    foreach ($pilihanJawaban as $index => $pilihanText) {
                        if (!empty(trim($pilihanText))) {
                            $pilihanToInsert[] = [
                                'id_pertanyaan' => $pertanyaanId,
                                'pilihan' => $pilihanText,
                                'urutan' => $index + 1
                            ];
                        }
                    }

                    if (!empty($pilihanToInsert)) {
                        $this->pmoPilihanJawabanModel->insertBatch($pilihanToInsert);
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->dataService->response(['status' => false, 'message' => 'Gagal menyimpan data.'], 500);
            }

            return $this->dataService->response($result, $result['code']);
        } catch (Exception $e) {
            $db->transRollback();
            return $this->dataService->response(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Hapus pertanyaan PMO
     */
    public function delete($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $pmoPertanyaanModel = $this->pmoPertanyaanModel;

        $db = Database::connect();
        $db->transStart();

        try {
            $deleted = $pmoPertanyaanModel->delete($id);
            $db->transComplete();

            if ($deleted) {
                return $this->dataService->response(['status' => true, 'message' => 'Pertanyaan PMO berhasil dihapus']);
            } else {
                return $this->dataService->response(['status' => false, 'message' => 'Gagal menghapus Pertanyaan PMO'], 400);
            }
        } catch (Exception $e) {
            $db->transRollback();
            return $this->dataService->response(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil data pertanyaan PMO berdasarkan ID (untuk modal edit)
     */
    public function getById($id = null): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return Services::response()->setStatusCode(404);
        }

        $pertanyaan = $this->pmoPertanyaanModel->find($id);

        if (!$pertanyaan) {
            return $this->dataService->response(['status' => false, 'message' => 'Pertanyaan PMO tidak ditemukan'], 404);
        }

        // Jika jenisnya pilihan ganda, ambil juga pilihan jawabannya
        if ($pertanyaan['jenis_jawaban'] === 'PILIHAN_GANDA') {
            $pertanyaan['pilihan'] = $this->pmoPilihanJawabanModel
                ->where('id_pertanyaan', $id)
                ->orderBy('urutan', 'ASC')
                ->findAll();
        }

        return $this->dataService->response(['status' => true, 'data' => $pertanyaan]);
    }
}
