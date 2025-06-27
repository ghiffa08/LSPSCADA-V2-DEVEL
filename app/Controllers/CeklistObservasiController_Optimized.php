<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ObservasiService;
use App\Services\AuthService;
use App\Requests\CreateObservasiRequest;
use App\Exceptions\ObservasiException;
use App\Utils\ApiResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Ceklist Observasi Controller
 * 
 * Handles observasi checklist operations for asesor
 * 
 * @package App\Controllers
 * @author LSP SCADA Team
 * @version 2.0
 */
class CeklistObservasiController extends BaseController
{
    private ObservasiService $observasiService;
    private AuthService $authService;

    public function __construct()
    {
        $this->observasiService = service('observasiService');
        $this->authService = service('authService');
    }

    /**
     * Display observasi creation form
     * 
     * @return string
     */
    public function create(): string
    {
        try {
            $asesorData = $this->authService->getCurrentAsesor();
            $data = $this->observasiService->getObservasiCreateData($asesorData);

            return view('asesor/ceklist_observasi', $data);
        } catch (ObservasiException $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Observasi creation failed: ' . $e->getMessage());
            }

            return view('asesor/ceklist_observasi', [
                'error' => $e->getMessage(),
                'siteTitle' => 'Ceklist Observasi'
            ]);
        }
    }

    /**
     * Store observasi data
     * 
     * @return ResponseInterface
     */
    public function store(): ResponseInterface
    {
        $request = new CreateObservasiRequest();

        if (!$request->validate()) {
            return $this->response->setJSON(
                ApiResponse::error('Validation failed', $request->getErrors())
            )->setStatusCode(422);
        }

        try {
            $asesorData = $this->authService->getCurrentAsesor();
            $result = $this->observasiService->createObservasi(
                $asesorData,
                $request->getValidatedData()
            );

            return $this->response->setJSON(
                ApiResponse::success($result, 'Observasi berhasil disimpan')
            );
        } catch (ObservasiException $e) {
            if (ENVIRONMENT === 'development') {
                log_message('error', 'Observasi store failed: ' . $e->getMessage());
            }

            return $this->response->setJSON(
                ApiResponse::error($e->getMessage())
            )->setStatusCode(500);
        }
    }

    /**
     * Display observasi list
     * 
     * @return string
     */
    public function index(): string
    {
        try {
            $asesorData = $this->authService->getCurrentAsesor();
            $data = $this->observasiService->getObservasiListData($asesorData);

            return view('asesor/observasi_list', $data);
        } catch (ObservasiException $e) {
            return view('asesor/observasi_list', [
                'error' => $e->getMessage(),
                'siteTitle' => 'Daftar Observasi'
            ]);
        }
    }

    /**
     * Show specific observasi
     * 
     * @param int $id
     * @return string
     */
    public function show(int $id): string
    {
        try {
            $asesorData = $this->authService->getCurrentAsesor();
            $data = $this->observasiService->getObservasiDetail($asesorData, $id);

            return view('asesor/observasi_detail', $data);
        } catch (ObservasiException $e) {
            return redirect()->to('/ceklist-observasi')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Generate PDF report
     * 
     * @param int $id
     * @return ResponseInterface
     */
    public function generatePDF(int $id): ResponseInterface
    {
        try {
            $asesorData = $this->authService->getCurrentAsesor();
            $pdfData = $this->observasiService->generatePDFReport($asesorData, $id);

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="observasi_' . $id . '.pdf"')
                ->setBody($pdfData);
        } catch (ObservasiException $e) {
            return $this->response->setJSON(
                ApiResponse::error('Gagal generate PDF: ' . $e->getMessage())
            )->setStatusCode(500);
        }
    }
}
