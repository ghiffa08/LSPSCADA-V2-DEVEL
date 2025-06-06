<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Controllers\BaseController;
use App\Services\PengajuanAsesmenService;
use App\Services\ValidationService;
use App\Services\CustomResponseService;
use App\DTOs\PengajuanAsesmenRequestDTO;
use App\Exceptions\ValidationException;
use Exception;

class PengajuanAsesmenController extends BaseController
{
    private PengajuanAsesmenService $pengajuanService;
    private ValidationService $validationService;
    private CustomResponseService $responseService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->pengajuanService = service('PengajuanAsesmenService');
        $this->validationService = new ValidationService();
        $this->responseService = service('CustomResponseService');
    }

    /**
     * Display the assessment application form
     *
     * @return string
     */
    public function index()
    {
        try {
            $data = [
                'siteTitle' => 'Pendaftaran Uji Kompetensi',
                'siteSubtitle' => 'Pada bagian ini, masukan data pribadi, data pendidikan formal, data pekerjaan Anda pada saat ini, serta dokumen pendukung.',
                'provinsi' => $this->dynamicDependentModel->AllProvinsi(),
                'listSkema' => $this->skemaModel->getActiveSchemes(),
                'listAsesmen' => $this->asesmenModel->getAllAsesmen(),
            ];
            return view('asesi/pengajuan-sertifikasi', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error loading pengajuan form: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat memuat halaman');
        }
    }

    /**
     * Store a new assessment application
     *
     * @return ResponseInterface
     */
    public function store()
    {
        try {
            $postData = $this->request->getPost();
            $files = $this->request->getFiles();
            $validationResult = $this->validationService->validatePengajuanAsesmen($postData);
            if (!$validationResult->success) {
                return $this->responseService->validationError($validationResult->errors, $validationResult->message);
            }
            $dto = new \App\DTOs\PengajuanAsesmenRequestDTO($postData, $files);
            $serviceResult = $this->pengajuanService->submitApplication($dto, $files);
            if ($serviceResult->success) {
                session()->setFlashdata('pesan', $serviceResult->message);
                return $this->responseService->success($serviceResult->data, $serviceResult->message);
            } else {
                session()->setFlashdata('error', $serviceResult->message);
                return $this->responseService->error($serviceResult->message, 400, $serviceResult->errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error during application submission: ' . $e->getMessage());
            session()->setFlashdata('error', 'Terjadi kesalahan saat memproses pengajuan: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat memproses pengajuan', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Show pengajuan details
     *
     * @param string $id
     * @return mixed
     */
    public function show(string $id)
    {
        try {
            $result = $this->pengajuanService->getApplicationById($id);
            if (!$result->success) {
                return $this->responseService->notFound($result->message);
            }
            $data = [
                'siteTitle' => 'Detail Pengajuan Asesmen',
                'pengajuan' => $result->data
            ];
            return view('asesi/pengajuan-detail', $data);
        } catch (\Exception $e) {
            log_message('error', 'Error showing pengajuan: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Get pengajuan data for DataTables
     *
     * @return ResponseInterface
     */
    public function getDataTables()
    {
        try {
            $draw = $this->request->getPost('draw') ?? 1;
            $start = $this->request->getPost('start') ?? 0;
            $length = $this->request->getPost('length') ?? 10;
            $searchValue = $this->request->getPost('search')['value'] ?? '';
            $orderColumn = $this->request->getPost('order')[0]['column'] ?? 0;
            $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'asc';
            $result = $this->pengajuanService->getDataTablesData([
                'draw' => $draw,
                'start' => $start,
                'length' => $length,
                'search' => $searchValue,
                'orderColumn' => $orderColumn,
                'orderDir' => $orderDir
            ]);
            return $this->responseService->dataTable($result['data'], $draw, $result['recordsTotal'] ?? 0, $result['recordsFiltered'] ?? 0);
        } catch (\Exception $e) {
            log_message('error', 'Error getting DataTables data: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat memuat data');
        }
    }

    /**
     * Update pengajuan status
     *
     * @param string $id
     * @return ResponseInterface
     */
    public function updateStatus(string $id)
    {
        try {
            $status = $this->request->getPost('status');
            $notes = $this->request->getPost('notes');
            $validator = user_id(); // Replace with actual user ID retrieval logic
            $result = $this->pengajuanService->updateApplicationStatus($id, $status, $validator, $notes);
            if ($result->success) {
                return $this->responseService->success($result->data, $result->message);
            } else {
                return $this->responseService->error('Gagal memperbarui status pengajuan', 400, $result->errors);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating pengajuan status: ' . $e->getMessage());
            return $this->responseService->error('Terjadi kesalahan saat memperbarui status');
        }
    }
}
