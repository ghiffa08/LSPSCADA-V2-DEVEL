<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ObservasiService;
use App\Services\AuthService;
use App\Services\PDFService;
use App\Services\QRCodeService;
use App\Exceptions\AuthException;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

/**
 * Optimized Ceklist Observasi Controller
 * Implements clean architecture with proper separation of concerns
 */
class CeklistObservasiController extends BaseController
{
    protected ObservasiService $observasiService;
    protected AuthService $authService;
    protected PDFService $pdfService;
    protected QRCodeService $qrCodeService;
    protected array $currentUser;

    public function __construct()
    {
        helper(['auth', 'form', 'url']);

        // Initialize services with dependency injection
        $this->observasiService = service('observasiService');
        $this->authService = service('authService');
        $this->pdfService = new PDFService();
        $this->qrCodeService = new QRCodeService();

        // Security check - Asesor or Admin only
        if (!$this->authService->hasRole(['Asesor', 'Admin'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }

        // Get current user info
        $this->currentUser = $this->authService->getCurrentUser();
    }

    /**
     * Display observasi dashboard for asesor
     */
    public function index(): string
    {
        try {
            $data = $this->prepareIndexData();
            return view('asesor/observasi/dashboard', $data);
        } catch (Exception $e) {
            log_message('error', 'Error in observasi index: ' . $e->getMessage());
            return $this->handleIndexError();
        }
    }

    /**
     * Show create observasi form
     */
    public function create($asesmenId = null): string
    {
        try {
            if ($asesmenId && !$this->authService->canAccessAsesmen($this->currentUser['id'], $asesmenId)) {
                throw AuthException::accessDenied('You do not have permission to access this asesmen');
            }

            $data = $this->prepareCreateData($asesmenId);
            return view('asesor/observasi/create', $data);
        } catch (Exception $e) {
            log_message('error', 'Error in observasi create: ' . $e->getMessage());
            return redirect()->to('/asesor/observasi')
                ->with('error', 'Unable to load create form: ' . $e->getMessage());
        }
    }

    /**
     * Store new observasi checklist
     */
    public function store(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405);
        }

        try {
            $validatedData = $this->validateObservasiRequest();
            $result = $this->observasiService->createObservasi($validatedData);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $result['message'],
                'data' => ['id' => $result['observasi_id']]
            ]);
        } catch (Exception $e) {
            return $this->handleStoreError($e);
        }
    }

    /**
     * Show observasi edit form
     */
    public function edit(int $observasiId): string
    {
        try {
            $observasi = $this->observasiService->getObservasiById($observasiId);
            $data = $this->prepareEditData($observasi);
            return view('asesor/observasi/edit', $data);
        } catch (Exception $e) {
            log_message('error', 'Error in observasi edit: ' . $e->getMessage());
            return redirect()->to('/asesor/observasi')
                ->with('error', 'Unable to load edit form: ' . $e->getMessage());
        }
    }

    /**
     * Update existing observasi
     */
    public function update(int $observasiId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405);
        }

        try {
            $validatedData = $this->validateObservasiRequest();
            $result = $this->observasiService->updateObservasi($observasiId, $validatedData);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $result['message']
            ]);
        } catch (Exception $e) {
            return $this->handleUpdateError($e);
        }
    }

    /**
     * Delete observasi
     */
    public function delete(int $observasiId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405);
        }

        try {
            $result = $this->observasiService->deleteObservasi($observasiId);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $result['message']
            ]);
        } catch (Exception $e) {
            return $this->handleDeleteError($e);
        }
    }

    /**
     * View observasi detail
     */
    public function view(int $observasiId): string
    {
        try {
            $observasi = $this->observasiService->getObservasiById($observasiId);
            $data = $this->prepareViewData($observasi);
            return view('asesor/observasi/view', $data);
        } catch (Exception $e) {
            log_message('error', 'Error in observasi view: ' . $e->getMessage());
            return redirect()->to('/asesor/observasi')
                ->with('error', 'Unable to load observasi: ' . $e->getMessage());
        }
    }

    /**
     * Submit observasi for review
     */
    public function submit(int $observasiId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405);
        }

        try {
            $result = $this->observasiService->submitObservasi($observasiId);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $result['message']
            ]);
        } catch (Exception $e) {
            return $this->handleSubmitError($e);
        }
    }

    /**
     * Generate PDF for observasi
     */
    public function pdf(int $observasiId): void
    {
        try {
            $observasi = $this->observasiService->getObservasiById($observasiId);
            $pdfData = $this->preparePdfData($observasi);
            $this->generatePdf($pdfData);
        } catch (Exception $e) {
            log_message('error', 'Error generating PDF: ' . $e->getMessage());
            session()->setFlashdata('error', 'Unable to generate PDF: ' . $e->getMessage());
            redirect()->to("/asesor/observasi/view/{$observasiId}");
        }
    }

    /**
     * Get observasi data via AJAX
     */
    public function getObservasiData(int $asesmenId): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405);
        }

        try {
            $data = $this->observasiService->getObservasiCreateData($asesmenId);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // PRIVATE HELPER METHODS

    /**
     * Prepare data for index view
     */
    private function prepareIndexData(): array
    {
        $summary = $this->observasiService->getObservasiSummary($this->currentUser['id']);

        return [
            'siteTitle' => 'Ceklist Observasi',
            'pageTitle' => 'Dashboard Observasi Asesor',
            'summary' => $summary,
            'userInfo' => $this->currentUser,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                ['label' => 'Observasi', 'url' => '', 'active' => true]
            ]
        ];
    }

    /**
     * Prepare data for create view
     */
    private function prepareCreateData($asesmenId = null): array
    {
        if ($asesmenId) {
            $createData = $this->observasiService->getObservasiCreateData($asesmenId);
        } else {
            // Get available asesmen for selection
            $createData = ['asesmen_list' => $this->getAvailableAsesmen()];
        }

        return array_merge($createData, [
            'siteTitle' => 'Buat Ceklist Observasi',
            'pageTitle' => 'Buat Ceklist Observasi Baru',
            'userInfo' => $this->currentUser,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                ['label' => 'Observasi', 'url' => '/asesor/observasi'],
                ['label' => 'Buat Ceklist', 'url' => '', 'active' => true]
            ]
        ]);
    }

    /**
     * Get available asesmen for current user
     */
    private function getAvailableAsesmen(): array
    {
        // This would typically be in the service layer
        // For now, return a placeholder
        return [];
    }

    /**
     * Prepare data for edit view
     */
    private function prepareEditData(array $observasi): array
    {
        $createData = $this->observasiService->getObservasiCreateData($observasi['asesmen_id']);

        return array_merge($createData, [
            'siteTitle' => 'Edit Ceklist Observasi',
            'pageTitle' => 'Edit Ceklist Observasi',
            'observasi' => $observasi,
            'userInfo' => $this->currentUser,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                ['label' => 'Observasi', 'url' => '/asesor/observasi'],
                ['label' => 'Edit', 'url' => '', 'active' => true]
            ]
        ]);
    }

    /**
     * Prepare data for view
     */
    private function prepareViewData(array $observasi): array
    {
        return [
            'siteTitle' => 'Detail Ceklist Observasi',
            'pageTitle' => 'Detail Ceklist Observasi',
            'observasi' => $observasi,
            'userInfo' => $this->currentUser,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/asesor/dashboard'],
                ['label' => 'Observasi', 'url' => '/asesor/observasi'],
                ['label' => 'Detail', 'url' => '', 'active' => true]
            ]
        ];
    }

    /**
     * Validate observasi request data
     */
    private function validateObservasiRequest(): array
    {
        $rules = [
            'asesmen_id' => [
                'label' => 'Asesmen',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'pertanyaan_observasi' => [
                'label' => 'Pertanyaan Observasi',
                'rules' => 'required'
            ],
            'jawaban' => [
                'label' => 'Jawaban',
                'rules' => 'required'
            ]
        ];

        if (!$this->validateData($this->request->getPost(), $rules)) {
            throw new Exception('Validation failed: ' . implode(', ', $this->validator->getErrors()));
        }

        return $this->request->getPost();
    }

    /**
     * Prepare PDF data
     */
    private function preparePdfData(array $observasi): array
    {
        $data = [
            'observasi' => $observasi,
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => $this->currentUser['nama']
        ];

        // Generate QR codes if signatures exist
        if (!empty($observasi['ttd_asesi'])) {
            $data['qr_asesi'] = $this->qrCodeService->generate(
                base_url('/scan-tanda-tangan-asesi/' . $observasi['ttd_asesi']),
                'logolsp.png'
            );
        }

        if (!empty($observasi['ttd_asesor'])) {
            $data['qr_asesor'] = $this->qrCodeService->generate(
                base_url('/scan-tanda-tangan-asesor/' . $observasi['ttd_asesor']),
                'logolsp.png'
            );
        }

        return $data;
    }

    /**
     * Generate and output PDF
     */
    private function generatePdf(array $data): void
    {
        $views = ['pdf/observasi_ceklist'];
        $filename = 'FR.IA.01_CEKLIST_OBSERVASI_' . date('Ymd_His');

        $this->pdfService->generateMultiPagePdf($views, $data, $filename);
    }

    // ERROR HANDLING METHODS

    /**
     * Handle index error
     */
    private function handleIndexError(): string
    {
        return view('asesor/observasi/dashboard', [
            'siteTitle' => 'Ceklist Observasi',
            'pageTitle' => 'Dashboard Observasi Asesor',
            'summary' => [],
            'userInfo' => $this->currentUser,
            'error' => 'Unable to load dashboard data'
        ]);
    }

    /**
     * Handle store error
     */
    private function handleStoreError(Exception $e): ResponseInterface
    {
        log_message('error', 'Error storing observasi: ' . $e->getMessage());

        $statusCode = ($e instanceof AuthException) ? 403 : 500;

        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    /**
     * Handle update error
     */
    private function handleUpdateError(Exception $e): ResponseInterface
    {
        log_message('error', 'Error updating observasi: ' . $e->getMessage());

        $statusCode = ($e instanceof AuthException) ? 403 : 500;

        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    /**
     * Handle delete error
     */
    private function handleDeleteError(Exception $e): ResponseInterface
    {
        log_message('error', 'Error deleting observasi: ' . $e->getMessage());

        $statusCode = ($e instanceof AuthException) ? 403 : 500;

        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    /**
     * Handle submit error
     */
    private function handleSubmitError(Exception $e): ResponseInterface
    {
        log_message('error', 'Error submitting observasi: ' . $e->getMessage());

        $statusCode = ($e instanceof AuthException) ? 403 : 500;

        return $this->response->setStatusCode($statusCode)->setJSON([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
}
