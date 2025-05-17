<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class KUKController extends BaseController
{
    /**
     * Display a listing of the KUK.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $data = [
            'siteTitle' => "Kelola Kriteria Unjuk Kerja",
            'listSkema' => $this->skemaModel->getActiveSchemes(),
        ];

        return view('admin/kuk', $data);
    }

    /**
     * Import KUK data from Excel
     */
    public function import()
    {
        $transformCallback = function ($row, $options) {
            return [
                'id_skema' => $row[0] ?? null,
                'id_unit' => $row[1] ?? null,
                'id_elemen' => $row[2] ?? null,
                'kode_kuk' => $row[3] ?? null,
                'nama_kuk' => $row[4] ?? null,
                'pertanyaan' => $row[5] ?? null,
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->kukModel,
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
     * Generate Excel template for KUK import
     */
    public function downloadTemplate()
    {
        // Define headers for the template
        $headers = [
            'ID Skema',
            'ID Unit',
            'ID Elemen',
            'Kode KUK',
            'Nama KUK',
            'Pertanyaan'
        ];


        $filename = 'template_kuk_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }
}
