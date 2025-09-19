<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PMOPertanyaanModel;
use CodeIgniter\HTTP\ResponseInterface;

class PmoPertanyaanController extends BaseController
{

    protected $pmoPertanyaanModel;

    public function __construct()
    {


        $this->pmoPertanyaanModel = new PMOPertanyaanModel();
    }

    /**
     * Menampilkan daftar Pertanyaan PMO.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $data = [
            'siteTitle' => "Kelola Pertanyaan PMO",
            'listSkema' => $this->skemaModel->getActiveSchemes(),
        ];

        return view('admin/pertanyaan_pmo', $data);
    }

    /**
     * Impor data Pertanyaan PMO dari Excel
     */
    public function import()
    {
        $transformCallback = function ($row, $options) {
            return [
                'id_skema' => $row[0] ?? null,
                'id_unit' => $row[1] ?? null,
                'id_elemen' => $row[2] ?? null,
                'id_kuk' => $row[3] ?? null,
                'pertanyaan' => $row[4] ?? null,
                'jenis_jawaban' => $row[5] ?? 'YA_TIDAK',
                'urutan' => $row[6] ?? 0,
                'aktif' => $row[7] ?? 'Y',
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->pmoPertanyaanModel, // Menggunakan model PmoPertanyaan
            [],
            $transformCallback
        );

        // ✅ Respons JSON untuk AJAX
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        // Respons redirect biasa (non-AJAX)
        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } elseif ($result['status'] === 'partial') {
            return redirect()->back()->with('warning', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Hasilkan template Excel untuk impor Pertanyaan PMO
     */
    public function downloadTemplate()
    {
        // Tentukan header untuk template
        $headers = [
            'ID Skema',
            'ID Unit',
            'ID Elemen',
            'ID KUK',
            'Pertanyaan',
            'Jenis Jawaban (YA_TIDAK/PILIHAN_GANDA/ESSAY)',
            'Urutan',
            'Aktif (Y/N)'
        ];


        $filename = 'template_pmo_pertanyaan_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }
}
