<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class SkemaController extends BaseController
{

    public function index(): string
    {
        $data = [
            'siteTitle' => 'Kelola Skema',
        ];

        return view('admin/skema', $data);
    }

    /**
     * Import Skema data from Excel
     */
    public function import()
    {
        $transformCallback = function ($row, $options) {
            return [
                'kode_skema'  => $row[0] ?? null,
                'nama_skema'  => $row[1] ?? null,
                'jenis_skema' => $row[2] ?? 'KKNI',
                'status'      => $row[3] ?? 'Y',
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->skemaModel,
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
            'Kode Skema',
            'Nama Skema',
            'Jenis Skema',
            'Status'
        ];


        $filename = 'template_skema_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }
}
