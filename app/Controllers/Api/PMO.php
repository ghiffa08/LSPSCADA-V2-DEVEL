<?php

namespace App\Controllers\Api;

use Config\Database;
use App\Models\PMOModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\DataTableController;
use Exception;

/**
 * PMO API Controller
 * * Handles AJAX requests for PMO checklist operations,
 * including loading questions, saving answers, and providing data for DataTables.
 */
class PMO extends DataTableController
{
    /**
     * PMO constructor.
     */
    public function __construct()
    {
        // Panggil constructor dari parent
        parent::__construct();

        // Set model yang akan digunakan
        $this->model = new PMOModel();

        // Definisikan pemetaan kolom untuk sorting server-side
        $this->columnMap = [
            0 => null, // Kolom Nomor
            1 => 'asesi_user.nama_lengkap',
            2 => 'skema.nama_skema',
            3 => 'asesor_user.nama_lengkap',
            4 => 'pmo.tanggal_observasi',
            5 => null // Kolom Aksi
        ];
    }

    /**
     * Menyediakan data untuk Server-Side DataTables.
     */
    public function list(): \CodeIgniter\HTTP\ResponseInterface
    {
        $request = service('request');
        $postData = $request->getPost();

        // Ambil parameter dari request DataTables
        $limit = (int) ($postData['length'] ?? 10);
        $start = (int) ($postData['start'] ?? 0);
        $search = $postData['search']['value'] ?? '';

        // Logika untuk ordering
        $orderColumnIndex = $postData['order'][0]['column'] ?? null;
        $orderDir = $postData['order'][0]['dir'] ?? 'asc';

        // Gunakan $this->columnMap yang sudah didefinisikan di constructor
        $orderColumn = $this->columnMap[$orderColumnIndex] ?? null;

        // Ambil filter kustom (jika ada)
        // Note: Trait Anda saat ini tidak mendukung filter ini,
        // tapi kita siapkan jika Anda ingin mengembangkannya nanti.
        $filters = [
            'rekaman_asesmen.id_asesor'      => $request->getPost('id_asesor'),
            'skema.id_skema'                 => $request->getPost('id_skema'),
            'rekaman_asesmen.tanggal_rekaman >=' => $request->getPost('tanggal_dari'),
            'rekaman_asesmen.tanggal_rekaman <=' => $request->getPost('tanggal_sampai'),
        ];
        // Untuk saat ini, $filters tidak digunakan karena trait tidak mendukungnya.

        // Panggil method dari trait dengan parameter yang benar
        $result = $this->model->getDataTable($limit, $start, $search, $orderColumn, $orderDir);

        // Ubah format output agar sesuai dengan yang diharapkan DataTables
        $output = [
            "draw"            => (int) ($postData['draw'] ?? 0),
            "recordsTotal"    => $result['total'],
            "recordsFiltered" => $result['filtered'],
            "data"            => $result['data'],
            "csrf_token"      => csrf_hash()
        ];

        return $this->respond($output);
    }

    public function loadPmo(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Direct access is not allowed.');
        }

        $id_skema = $this->request->getGet('id_skema');
        $id_pengajuan = $this->request->getGet('id_pengajuan'); // Changed from id_apl1

        if (!$id_skema || !filter_var($id_skema, FILTER_VALIDATE_INT)) {
            return $this->fail('ID Skema is required and must be an integer.', 400);
        }
        if (!$id_pengajuan) {
            return $this->fail('ID Pengajuan is required.', 400);
        }

        try {
            $strukturPmo = $this->model->getStrukturPmoSkema((int)$id_skema);

            // Find PMO session by id_pengajuan
            $pmo = $this->model->where('id_pengajuan', $id_pengajuan)->first();
            $existingJawaban = [];
            if ($pmo) {
                $existingJawaban = $this->model->getExistingJawaban($pmo['id_pmo']);
            }

            return $this->respond([
                'success' => true,
                'struktur' => $strukturPmo,
                'pmo_data' => $pmo,
                'existing_jawaban' => $existingJawaban
            ]);
        } catch (Exception $e) {
            log_message('error', '[PMO API] loadPmo error: ' . $e->getMessage());
            return $this->fail('Failed to load PMO data: ' . $e->getMessage(), 500);
        }
    }

    public function save(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Direct access is not allowed.');
        }

        $data = $this->request->getPost();

        // Basic validation
        $id_pengajuan = $data['id_pengajuan'] ?? null; // Changed from id_apl1
        $id_skema = $data['id_skema'] ?? null;
        $id_asesor = $data['id_asesor'] ?? null;

        if (!$id_pengajuan || !$id_skema || !$id_asesor) {
            return $this->fail('Pengajuan ID, Skema ID, and Asesor ID are required.', 400);
        }

        $masterData = [
            'id_pengajuan' => $id_pengajuan,
            'id_skema' => (int)$id_skema,
            'id_asesor' => (int)$id_asesor,
            'tanggal_observasi' => $data['tanggal_observasi'] ?? date('Y-m-d'),
            'catatan' => $data['catatan'] ?? null,
        ];

        $jawabanData = $data['jawaban'] ?? [];

        try {
            $id_pmo = $this->model->savePmoData($masterData, $jawabanData);

            if ($id_pmo) {
                return $this->respondCreated([
                    'success' => true,
                    'message' => 'PMO checklist saved successfully.',
                    'id_pmo' => $id_pmo
                ]);
            } else {
                return $this->fail('Failed to save PMO data due to a transaction error.', 500);
            }
        } catch (Exception $e) {
            log_message('error', '[PMO API] save error: ' . $e->getMessage());
            return $this->fail('An error occurred while saving: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id_pmo = null): ResponseInterface
    {
        // This method works with id_pmo, so no changes are needed.
        if (!$this->request->isAJAX()) {
            return $this->failForbidden('Direct access is not allowed.');
        }

        if (!$id_pmo || !filter_var($id_pmo, FILTER_VALIDATE_INT)) {
            return $this->fail('Valid PMO ID is required.', 400);
        }

        try {
            if ($this->model->delete($id_pmo)) {
                return $this->respondDeleted(['success' => true, 'message' => 'PMO session deleted successfully.']);
            } else {
                return $this->fail('Failed to delete the PMO session.', 500);
            }
        } catch (Exception $e) {
            log_message('error', '[PMO API] delete error: ' . $e->getMessage());
            return $this->fail('An error occurred during deletion: ' . $e->getMessage(), 500);
        }
    }
}
