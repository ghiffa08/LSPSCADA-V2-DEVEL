<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class KomponenFeedbackController extends BaseController
{
    public function index(): string
    {
        $data = [
            'siteTitle' => 'Kelola Komponen Umpan Balik',
        ];

        return view('admin/manage_komponen_feedback', $data);
    }

    /**
     * Import komponen feedback data from Excel
     */
    public function import(): ResponseInterface|RedirectResponse
    {
        $transformCallback = function ($row, $options) {
            return [
                'pernyataan' => $row[0] ?? null,
                'urutan'     => $row[1] ?? 0,
            ];
        };

        $result = $this->importService->import(
            $this->request,
            $this->komponenUmpanBalikModel,
            [],
            $transformCallback
        );

        // AJAX response
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($result);
        }

        // Redirect if not AJAX
        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } elseif ($result['status'] === 'partial') {
            return redirect()->back()->with('warning', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Generate Excel template for Komponen Feedback import
     */
    public function downloadTemplate(): void
    {
        // Column headers matching the transform callback order
        $headers = [
            'Pernyataan Umpan Balik',
            'Urutan'
        ];

        $filename = 'template_komponen_feedback_import_' . date('Y-m-d') . '.xlsx';

        $this->importService->generateTemplate($headers, $filename);
    }
}
