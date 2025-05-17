<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UnitController extends BaseController
{

    /**
     * Display all Unit data
     */
    public function index(): string
    {
        $data = [
            'siteTitle' => 'Kelola Unit',
            'listUnit' => $this->unitModel->findAll(),
            'listSkema' => $this->skemaModel->getActiveSchemes()
        ];

        return view('admin/unit', $data);
    }

    /**
     * Import Skema data from Excel
     */
    public function import()
    {
        $transformCallback = function ($row, $options) {
            return [
                'id_skema'   => $row[0] ?? null,
                'kode_unit'  => $row[1] ?? null,
                'nama_unit'  => $row[2] ?? null,
                'keterangan' => $row[3] ?? null,
                'status'     => $row[4] ?? 'Y',
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->unitModel,
            [],
            $transformCallback
        );

        // Respons AJAX
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        // Redirect jika bukan AJAX
        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } elseif ($result['status'] === 'partial') {
            return redirect()->back()->with('warning', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Generate Excel template for Skema import
     */
    public function downloadTemplate()
    {
        // Header kolom sesuai urutan dalam transformCallback
        $headers = [
            'ID Skema',
            'Kode Unit',
            'Nama Unit',
            'Keterangan',
            'Status'
        ];

        $filename = 'template_unit_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }
}
