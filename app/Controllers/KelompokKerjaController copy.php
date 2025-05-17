<?php

namespace App\Controllers;

use Config\Database;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\API\ResponseTrait;

class KelompokKerjaController extends BaseController
{
    use ResponseTrait;
    protected $db;

    public function __construct()
    {
        $this->db = $db = Database::connect();
    }

    public function index()
    {
        $data = [
            'siteTitle'     => "Kelola Subelemen",
            'listSubelemen' => $this->subelemen->findAll(),
            'listSkema'     => $this->skema->AllSkema(),
            'listUnit'      => $this->unit->AllUnit(),
            'listElemen'    => $this->elemen->AllElemen(),
            'listKelompokKerja' => $this->kelompok_kerja_model->getAllKelompokWithSkemaAndUnits(),
        ];
        return view('admin/kelompok_kerja', $data);
    }

    /**
     * Get data for DataTable
     */
    public function getDataTable()
    {
        if (!$this->request->isAJAX()) {
            return $this->failUnauthorized('Akses ditolak');
        }

        $limit = $this->request->getVar('length');
        $start = $this->request->getVar('start');
        $search = $this->request->getVar('search')['value'];

        $order = $this->request->getVar('order')[0];
        $orderColumn = $this->request->getVar('columns')[$order['column']]['data'];
        $orderDir = $order['dir'];

        $result = $this->kelompok_kerja_model->getDataTable($limit, $start, $search, $orderColumn, $orderDir);

        return $this->respond([
            'draw' => $this->request->getVar('draw'),
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['data']
        ]);
    }

    /**
     * Get details of a specific kelompok kerja
     */
    public function detail($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Permintaan tidak valid', 400);
        }

        if (!$id) {
            return $this->fail('ID Kelompok Kerja tidak ditemukan', 404);
        }

        try {
            // Get main kelompok data
            $kelompokData = $this->kelompok_kerja_model->find($id);
            if (!$kelompokData) {
                return $this->fail('Kelompok Kerja tidak ditemukan', 404);
            }

            // Get unit data for this kelompok
            $units = $this->kelompok_unit_model->getUnitsByKelompokId($id);

            // Format the data
            $result = [
                [
                    'id_kelompok' => $kelompokData['id_kelompok'],
                    'nama_kelompok' => $kelompokData['nama_kelompok'],
                    'id_skema' => $kelompokData['id_skema'],
                    'units' => $units
                ]
            ];

            return $this->respond([
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
        if (!$this->request->isAJAX()) {
            return $this->fail('Permintaan tidak valid', 400);
        }

        // Validate CSRF token
        if (!$this->validateRequest(['<?= csrf_token() ?>' => 'required'], ['<?= csrf_token() ?>.required' => 'CSRF token tidak valid'])) {
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
                    $this->kelompok_kerja_model->update($id_kelompok, [
                        'id_skema' => $data['id_skema'],
                        'nama_kelompok' => $data['nama_kelompok']
                    ]);

                    // Delete existing units for this kelompok
                    $this->kelompok_unit_model->where('id_kelompok', $id_kelompok)->delete();
                } else {
                    // Insert new kelompok
                    $this->kelompok_kerja_model->insert([
                        'id_skema' => $data['id_skema'],
                        'nama_kelompok' => $data['nama_kelompok']
                    ]);
                    $id_kelompok = $this->kelompok_kerja_model->getInsertID();
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
                        $this->kelompok_unit_model->insertBatch($unitBatch);
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

    /**
     * Delete kelompok kerja
     */
    public function delete($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Permintaan tidak valid', 400);
        }

        if (!$id) {
            return $this->fail('ID Kelompok Kerja tidak ditemukan', 404);
        }

        $this->db->transStart();

        try {
            // Delete related units first
            $this->kelompok_unit_model->where('id_kelompok', $id)->delete();

            // Delete the kelompok
            $this->kelompok_kerja_model->delete($id);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new DatabaseException('Transaksi gagal dihapus.');
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error deleting kelompok kerja: ' . $e->getMessage());
            return $this->fail('Gagal menghapus data: ' . $e->getMessage(), 500);
        }
    }
}
