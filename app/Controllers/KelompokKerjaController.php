<?php

namespace App\Controllers;

use Config\Database;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use App\Controllers\DataTableController;


class KelompokKerjaController extends DataTableController
{
    use ResponseTrait;

    protected $db;
    protected $kukModel;
    protected $skemaModel;
    protected $unitModel;
    protected $elemenModel;
    protected $kelompokUnitModel;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect();

        $this->model = $this->kelompokKerjaModel;

        // Optional: Define custom column mapping for complex ordering
        $this->columnMap = [
            0 => null, // No ordering for index column
            1 => 'kelompok_kerja.nama_kelompok',
            2 => 'skema.nama_skema',
            3 => 'jumlah_unit',
            4 => null // No ordering for action column
        ];
    }

    public function index()
    {
        $data = [
            'siteTitle'     => "Kelola Kelompok Kerja",
            'listSubelemen' => $this->kukModel->findAll(),
            'listSkema'     => $this->skemaModel->getActiveSchemes(),
            'listUnit'      => $this->unitModel->getActiveUnits(),
            'listElemen'    => $this->elemenModel->getAllElements(),
            // 'listKelompokKerja' => $this->model->getAllKelompokWithSkemaAndUnits(),
        ];
        // $dump = $this->model->getDataTable(10, null,  null, null, null);
        // print_r($dump);
        // die();

        return view('admin/kelompok_kerja', $data);
    }

    /**
     * Get details of a specific kelompok kerja
     */
    public function detail($id = null)
    {
        // if (!$this->request->isAJAX()) {
        //     return $this->fail('Permintaan tidak valid', 400);
        // }

        if (!$id) {
            return $this->fail('ID Kelompok Kerja tidak ditemukan', 404);
        }

        try {
            // Get main kelompok data
            $kelompokData = $this->model->find($id);
            if (!$kelompokData) {
                return $this->fail('Kelompok Kerja tidak ditemukan', 404);
            }

            // Get unit data for this kelompok
            $units = $this->kelompokUnitModel->getUnitsByKelompokId($id);

            // Format the data
            $result = [
                [
                    'id_kelompok' => $kelompokData['id_kelompok'],
                    'nama_kelompok' => $kelompokData['nama_kelompok'],
                    'id_skema' => $kelompokData['id_skema'],
                    'units' => $units
                ]
            ];

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->fail('Terjadi kesalahan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Save kelompok kerja (both add and edit)
     */
    public function save()
    {
        // if (!$this->request->isAJAX()) {
        //     return $this->fail('Permintaan tidak valid', 400);
        // }

        // Validate CSRF token
        $tokenName = csrf_token(); // misalnya: 'csrf_test_name'
        $tokenValue = $this->request->getPost($tokenName);

        if (!$tokenValue || $tokenValue !== csrf_hash()) {
            return $this->fail('CSRF token tidak valid', 403);
        }


        $mode = $this->request->getPost('mode');
        $kelompokData = $this->request->getPost('kelompok');

        if (empty($kelompokData)) {
            return $this->fail('Tidak ada data kelompok yang dikirim', 400);
        }

        $this->db->transStart();

        try {
            foreach ($kelompokData as $data) {
                if ($mode === 'edit' && !empty($data['id_kelompok'])) {
                    // Update existing kelompok
                    $id_kelompok = $data['id_kelompok'];
                    $this->model->update($id_kelompok, [
                        'id_skema' => $data['id_skema'],
                        'nama_kelompok' => $data['nama_kelompok']
                    ]);

                    // Delete existing units for this kelompok
                    $this->kelompokUnitModel->where('id_kelompok', $id_kelompok)->delete();
                } else {
                    // Insert new kelompok
                    $this->model->insert([
                        'id_skema' => $data['id_skema'],
                        'nama_kelompok' => $data['nama_kelompok']
                    ]);
                    $id_kelompok = $this->model->getInsertID();
                }

                // Insert units for this kelompok
                $unitBatch = [];
                if (!empty($data['unit_ids'])) {
                    foreach ($data['unit_ids'] as $unitId) {
                        if (!empty($unitId)) {
                            $unitBatch[] = [
                                'id_kelompok' => $id_kelompok,
                                'id_unit' => $unitId
                            ];
                        }
                    }

                    if (!empty($unitBatch)) {
                        $this->kelompokUnitModel->insertBatch($unitBatch);
                    }
                }
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaksi gagal disimpan.');
            }

            return $this->respond([
                'status' => 'success',
                'message' => $mode === 'edit' ? 'Data berhasil diperbarui' : 'Data berhasil disimpan'
            ]);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error saving kelompok kerja: ' . $e->getMessage());
            return $this->fail('Gagal menyimpan data: ' . $e->getMessage(), 500);
        }
    }

    // public function delete($id_kelompok_kerja)
    // {
    //     if (empty($id_kelompok_kerja)) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'ID Kelompok tidak valid'
    //         ])->setStatusCode(400);
    //     }

    //     $this->db->transBegin();

    //     try {
    //         // Hapus unit terkait
    //         $this->kelompokUnitModel->where('id_kelompok', $id_kelompok_kerja)->delete();

    //         // Hapus kelompok kerja
    //         $deleted = $this->model->delete($id_kelompok_kerja);

    //         if (!$deleted) {
    //             throw new \Exception('Gagal menghapus kelompok kerja');
    //         }

    //         $this->db->transCommit();

    //         return $this->response->setJSON([
    //             'status' => 'success',
    //             'message' => 'Kelompok kerja berhasil dihapus'
    //         ]);
    //     } catch (\Exception $e) {
    //         $this->db->transRollback();
    //         log_message('error', 'Error deleting kelompok kerja: ' . $e->getMessage());

    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'Terjadi kesalahan saat menghapus kelompok kerja'
    //         ])->setStatusCode(500);
    //     }
    // }
}
