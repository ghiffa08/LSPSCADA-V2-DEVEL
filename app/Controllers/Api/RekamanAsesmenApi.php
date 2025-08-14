<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\RekamanAsesmenModel;
use App\Models\RekamanAsesmenKompetensiModel;

/**
 * API Controller for Rekaman Asesmen (AJAX)
 *
 * Menyediakan endpoint AJAX untuk kebutuhan frontend rekaman asesmen,
 * dengan pola dan best practices seperti ObservasiService.
 */
class RekamanAsesmenApi extends ResourceController
{
    protected RekamanAsesmenModel $rekamanModel;
    protected RekamanAsesmenKompetensiModel $kompetensiModel;

    public function __construct()
    {
        $this->rekamanModel = new RekamanAsesmenModel();
        $this->kompetensiModel = new RekamanAsesmenKompetensiModel();
    }

    /**
     * Get rekaman + kompetensi (detail) untuk form edit/view
     * GET /api/rekaman/{id}
     */
    public function show($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Unauthorized', 401);
        }
        if (!$id || !is_numeric($id)) {
            return $this->fail('ID rekaman tidak valid');
        }
        $data = $this->rekamanModel->getRekamanWithDetails((int)$id);
        if (!$data) {
            return $this->fail('Data rekaman tidak ditemukan');
        }
        return $this->respond([
            'success' => true,
            'data' => $data,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Get progress statistik untuk dashboard
     * GET /api/rekaman/{id}/progress
     */
    public function progress($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Unauthorized', 401);
        }
        if (!$id || !is_numeric($id)) {
            return $this->fail('ID rekaman tidak valid');
        }
        $stats = $this->rekamanModel->getProgressStats((int)$id);
        return $this->respond([
            'success' => true,
            'data' => $stats,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Batch update kompetensi (import/save)
     * POST /api/rekaman/{id}/kompetensi
     * Body: array kompetensiRows
     */
    public function batchKompetensi($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Unauthorized', 401);
        }
        $kompetensiRows = $this->request->getJSON(true);
        if (!$id || !is_numeric($id) || empty($kompetensiRows) || !is_array($kompetensiRows)) {
            return $this->fail('Data tidak valid');
        }
        $result = $this->rekamanModel->importKompetensiBatch((int)$id, $kompetensiRows);
        if ($result) {
            return $this->respond([
                'success' => true,
                'message' => 'Batch kompetensi berhasil disimpan',
                'csrf_hash' => csrf_hash()
            ]);
        }
        return $this->fail('Gagal menyimpan batch kompetensi');
    }

    /**
     * Export rekaman + kompetensi (CSV/Excel-ready array)
     * GET /api/rekaman/{id}/export
     */
    public function export($id = null)
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Unauthorized', 401);
        }
        if (!$id || !is_numeric($id)) {
            return $this->fail('ID rekaman tidak valid');
        }
        $rows = $this->rekamanModel->exportRekamanWithKompetensi((int)$id);
        if (!$rows) {
            return $this->fail('Data tidak ditemukan');
        }
        return $this->respond([
            'success' => true,
            'data' => $rows,
            'csrf_hash' => csrf_hash()
        ]);
    }

    /**
     * Get list rekaman (filter, pagination)
     * GET /api/rekaman/list
     */
    public function list()
    {
        if (!$this->request->isAJAX()) {
            return $this->fail('Unauthorized', 401);
        }
        $filters = $this->request->getGet();
        $page = (int)($filters['page'] ?? 1);
        $perPage = (int)($filters['perPage'] ?? 10);
        unset($filters['page'], $filters['perPage']);
        $data = $this->rekamanModel->getRekamanList($filters, $page, $perPage);
        return $this->respond([
            'success' => true,
            'data' => $data,
            'csrf_hash' => csrf_hash()
        ]);
    }
}
