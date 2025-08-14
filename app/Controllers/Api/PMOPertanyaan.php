<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PMOPertanyaanModel;
use App\Models\UnitModel;

class PMOPertanyaan extends ResourceController
{
    protected $pmopertanyaanModel;
    protected $unitModel;

    public function __construct()
    {
        $this->pmopertanyaanModel = new PMOPertanyaanModel();
        $this->unitModel = new UnitModel();
    }

    public function save()
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Endpoint not found');
        }

        $validation = \Config\Services::validation();

        $rules = [
            'id_unit' => 'required|integer',
            'pertanyaan' => 'required|min_length[10]|max_length[1000]',
            'jenis_jawaban' => 'required|in_list[ya_tidak,pilihan_ganda,essay]',
            'urutan' => 'permit_empty|integer',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->fail($validation->getErrors());
        }

        try {
            $data = [
                'id_unit' => $this->request->getPost('id_unit'),
                'kuk_reference' => $this->request->getPost('kuk_reference'),
                'pertanyaan' => $this->request->getPost('pertanyaan'),
                'jenis_jawaban' => $this->request->getPost('jenis_jawaban'),
                'urutan' => $this->request->getPost('urutan') ?: 0,
                'is_active' => $this->request->getPost('is_active') ?? 1,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle pilihan_jawaban for multiple choice
            if ($data['jenis_jawaban'] === 'pilihan_ganda') {
                $pilihan = $this->request->getPost('pilihan_jawaban');
                if (is_array($pilihan)) {
                    $data['pilihan_jawaban'] = json_encode(array_filter($pilihan));
                } else {
                    $data['pilihan_jawaban'] = json_encode(explode(',', $pilihan));
                }
            } else {
                $data['pilihan_jawaban'] = null;
            }

            // Check if editing or creating new
            $id = $this->request->getPost('id');

            if ($id) {
                // Check if exists
                $existing = $this->pmopertanyaanModel->find($id);
                if (!$existing) {
                    return $this->failNotFound('Data tidak ditemukan');
                }

                // Check for duplicates (excluding current record)
                $duplicate = $this->pmopertanyaanModel
                    ->where('id_unit', $data['id_unit'])
                    ->where('pertanyaan', $data['pertanyaan'])
                    ->where('id !=', $id)
                    ->first();

                if ($duplicate) {
                    return $this->fail(['pertanyaan' => 'Pertanyaan sudah ada untuk unit ini']);
                }

                $this->pmopertanyaanModel->update($id, $data);
                $message = 'Data pertanyaan PMO berhasil diperbarui';
            } else {
                // Check for duplicates
                $duplicate = $this->pmopertanyaanModel
                    ->where('id_unit', $data['id_unit'])
                    ->where('pertanyaan', $data['pertanyaan'])
                    ->first();

                if ($duplicate) {
                    return $this->fail(['pertanyaan' => 'Pertanyaan sudah ada untuk unit ini']);
                }

                $data['created_at'] = date('Y-m-d H:i:s');
                $this->pmopertanyaanModel->insert($data);
                $message = 'Data pertanyaan PMO berhasil ditambahkan';
            }

            return $this->respondCreated([
                'status' => 'success',
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return $this->fail('Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function getById($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Endpoint not found');
        }

        try {
            $data = $this->pmopertanyaanModel->getPertanyaanWithUnit($id);

            if (!$data) {
                return $this->failNotFound('Data tidak ditemukan');
            }

            // Decode pilihan_jawaban if exists
            if ($data['pilihan_jawaban']) {
                $data['pilihan_jawaban'] = json_decode($data['pilihan_jawaban'], true);
            }

            return $this->respond([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->fail('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Endpoint not found');
        }

        try {
            $pertanyaan = $this->pmopertanyaanModel->find($id);

            if (!$pertanyaan) {
                return $this->failNotFound('Data tidak ditemukan');
            }

            // Check if pertanyaan is used in any PMO jawaban
            $db = \Config\Database::connect();
            $usageCount = $db->table('pmo_jawaban')
                ->where('id_template_pertanyaan', $id)
                ->countAllResults();

            if ($usageCount > 0) {
                return $this->fail('Pertanyaan tidak dapat dihapus karena sudah digunakan dalam PMO');
            }

            $this->pmopertanyaanModel->delete($id);

            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Data pertanyaan PMO berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return $this->fail('Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function getDataTable()
    {
        if (!$this->request->isAJAX()) {
            return $this->failNotFound('Endpoint not found');
        }

        try {
            $request = service('request');

            // DataTables parameters
            $draw = $request->getPost('draw');
            $start = $request->getPost('start') ?: 0;
            $length = $request->getPost('length') ?: 10;
            $searchValue = $request->getPost('search')['value'] ?? '';
            $orderColumn = $request->getPost('order')[0]['column'] ?? 0;
            $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';

            // Column mapping
            $columns = ['id', 'kode_unit', 'nama_unit', 'pertanyaan', 'jenis_jawaban', 'is_active', 'created_at'];
            $orderBy = $columns[$orderColumn] ?? 'id';

            // Get data
            $result = $this->pmopertanyaanModel->getDataTable($start, $length, $searchValue, $orderBy, $orderDir);

            $data = [];
            foreach ($result['data'] as $row) {
                $data[] = [
                    'id' => $row['id'],
                    'kode_unit' => $row['kode_unit'],
                    'nama_unit' => $row['nama_unit'],
                    'kuk_reference' => $row['kuk_reference'],
                    'pertanyaan' => $row['pertanyaan'],
                    'jenis_jawaban' => ucfirst(str_replace('_', ' ', $row['jenis_jawaban'])),
                    'urutan' => $row['urutan'],
                    'is_active' => $row['is_active'],
                    'created_at' => $row['created_at']
                ];
            }

            return $this->respond([
                'draw' => intval($draw),
                'recordsTotal' => $result['total'],
                'recordsFiltered' => $result['filtered'],
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->fail('Gagal mengambil data: ' . $e->getMessage());
        }
    }
}
