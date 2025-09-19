<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PertanyaanTertulisSoalController extends BaseController
{
    /**
     * Menampilkan halaman daftar bank soal.
     */
    public function index()
    {
        $data = [
            'siteTitle' => "Kelola Bank Soal Tertulis",
            'listSkema' => $this->skemaModel->getActiveSchemes(),
        ];

        return view('admin/pertanyaan_tertulis_soal', $data);
    }

    /**
     * Impor data soal dari file Excel.
     */
    public function import()
    {
        $transformCallback = function ($row, $options) {
            return [
                'id_skema' => $row[0] ?? null,
                'soal' => $row[1] ?? null,
                'jenis_soal' => $row[2] ?? 'PILIHAN_GANDA',
                'urutan' => $row[3] ?? 0,
                'aktif' => $row[4] ?? 'Y',
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->pertanyaanTertulisSoalModel, // Menggunakan model yang sesuai
            [],
            $transformCallback
        );

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } elseif ($result['status'] === 'partial') {
            return redirect()->back()->with('warning', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Hasilkan template Excel untuk impor soal.
     */
    public function downloadTemplate()
    {
        $headers = [
            'ID Skema',
            'Teks Soal',
            'Jenis Soal (PILIHAN_GANDA/ESSAY/BENAR_SALAH)',
            'Urutan',
            'Aktif (Y/N)'
        ];

        $filename = 'template_soal_tertulis_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }
}
