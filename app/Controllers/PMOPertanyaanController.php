<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PMOPertanyaanModel;
use App\Models\UnitModel;
use App\Models\SkemaModel;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PMOPertanyaanController extends BaseController
{
    protected $pmopertanyaanModel;
    protected $unitModel;
    protected $skemaModel;

    public function __construct()
    {
        $this->pmopertanyaanModel = new PMOPertanyaanModel();
        $this->unitModel = new UnitModel();
        $this->skemaModel = new SkemaModel();
    }

    public function index()
    {
        // Check permission

        $data = [
            'siteTitle' => 'Master Pertanyaan PMO',
            'menu' => 'master',
            'submenu' => 'pertanyaan-pmo',
            'listSkema' => $this->skemaModel->where('status', 'Y')->findAll(), // PERBAIKAN: Filter aktif
            'listUnit' => [], // Will be loaded via AJAX
        ];

        return view('admin/pertanyaan_pmo', $data);
    }

    public function import()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back();
        }

        $validation = \Config\Services::validation();

        $validation->setRules([
            'file_excel' => 'uploaded[file_excel]|ext_in[file_excel,xls,xlsx]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'File excel tidak valid'
            ]);
        }

        try {
            $file = $this->request->getFile('file_excel');

            if (!$file->isValid()) {
                throw new \Exception('File tidak valid');
            }

            // Read Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $imported = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed

                if (empty($row[0]) && empty($row[1]) && empty($row[2])) {
                    continue; // Skip empty rows
                }

                try {
                    // Validate required fields
                    if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                        $errors[] = "Baris {$rowNumber}: Data tidak lengkap";
                        continue;
                    }

                    // Check if unit exists
                    $unit = $this->unitModel->where('kode_unit', $row[0])->first();
                    if (!$unit) {
                        $errors[] = "Baris {$rowNumber}: Unit dengan kode '{$row[0]}' tidak ditemukan";
                        continue;
                    }

                    $data = [
                        'id_unit' => $unit['id_unit'],
                        'kuk_reference' => trim($row[1]) ?: null,
                        'pertanyaan' => trim($row[2]),
                        'jenis_jawaban' => !empty($row[3]) ? trim($row[3]) : 'ya_tidak',
                        'pilihan_jawaban' => !empty($row[4]) ? json_encode(explode(',', $row[4])) : null,
                        'urutan' => !empty($row[5]) ? (int)$row[5] : 0,
                        'is_active' => !empty($row[6]) ? ((int)$row[6] == 1 ? 1 : 0) : 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    // Check for duplicates
                    $existing = $this->pmopertanyaanModel
                        ->where('id_unit', $data['id_unit'])
                        ->where('pertanyaan', $data['pertanyaan'])
                        ->first();

                    if ($existing) {
                        $errors[] = "Baris {$rowNumber}: Pertanyaan sudah ada untuk unit ini";
                        continue;
                    }

                    $this->pmopertanyaanModel->insert($data);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            $message = "Import berhasil: {$imported} data berhasil diimport";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " data gagal diimport.";
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message,
                'imported' => $imported,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal import data: ' . $e->getMessage()
            ]);
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $headers = [
            'A1' => 'Kode Unit',
            'B1' => 'Referensi KUK',
            'C1' => 'Pertanyaan',
            'D1' => 'Jenis Jawaban',
            'E1' => 'Pilihan Jawaban',
            'F1' => 'Urutan',
            'G1' => 'Status Aktif'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Add sample data
        $sheet->setCellValue('A2', 'J.620100.004.01');
        $sheet->setCellValue('B2', 'KUK 1.1');
        $sheet->setCellValue('C2', 'Apakah asesi mampu mengidentifikasi struktur data yang tepat?');
        $sheet->setCellValue('D2', 'ya_tidak');
        $sheet->setCellValue('E2', '');
        $sheet->setCellValue('F2', '1');
        $sheet->setCellValue('G2', '1');

        // Auto-size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Add instructions
        $sheet->setCellValue('A4', 'PETUNJUK:');
        $sheet->setCellValue('A5', '1. Kode Unit: Masukkan kode unit yang sudah ada di database');
        $sheet->setCellValue('A6', '2. Referensi KUK: Opsional, referensi ke KUK tertentu');
        $sheet->setCellValue('A7', '3. Pertanyaan: Teks pertanyaan untuk PMO');
        $sheet->setCellValue('A8', '4. Jenis Jawaban: ya_tidak / pilihan_ganda / essay');
        $sheet->setCellValue('A9', '5. Pilihan Jawaban: Untuk pilihan ganda, pisahkan dengan koma');
        $sheet->setCellValue('A10', '6. Urutan: Nomor urut pertanyaan (opsional)');
        $sheet->setCellValue('A11', '7. Status Aktif: 1 = Aktif, 0 = Tidak Aktif');

        $filename = 'Template_Pertanyaan_PMO_' . date('Y-m-d') . '.xlsx';

        // Set headers for download
        $this->response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->response->setHeader('Cache-Control', 'max-age=0');

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $this->response->setBody($content);
    }
}
