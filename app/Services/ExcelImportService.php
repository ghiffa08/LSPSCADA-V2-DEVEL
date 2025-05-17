<?php

namespace App\Services;

use Config\Services;
use CodeIgniter\Model;
use CodeIgniter\HTTP\IncomingRequest;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelImportService
{
    /**
     * Import data from Excel file
     * 
     * @param IncomingRequest $request Request object
     * @param Model $model Model to insert data
     * @param array $validationRules Custom validation rules for file upload
     * @param callable|null $dataTransformCallback Callback to transform row data
     * @param array $options Additional options for import
     * @return array Import result
     */
    public function import(
        IncomingRequest $request,
        Model $model,
        array $validationRules = [],
        ?callable $dataTransformCallback = null,
        array $options = []
    ): array {
        // Default validation rules
        $defaultRules = [
            'file_excel' => [
                'label' => 'File Excel',
                'rules' => 'uploaded[file_excel]|max_size[file_excel,10240]|ext_in[file_excel,xls,xlsx]',
                'errors' => [
                    'uploaded' => 'Harus mengunggah {field}',
                    'max_size' => 'Ukuran file maksimal 10MB',
                    'ext_in' => 'Format file harus Excel (xls atau xlsx)'
                ],
            ],
        ];

        // Merge default rules with custom rules
        $rules = array_merge($defaultRules, $validationRules);

        // Validate file
        $validation = Services::validation();

        if (!$validation->setRules($rules)->run($request->getPost())) {
            return [
                'status' => 'error',
                'message' => $validation->getErrors()
            ];
        }

        // Get uploaded file
        $file = $request->getFile('file_excel');

        try {
            // Load spreadsheet
            $spreadsheet = IOFactory::load($file);
            $rows = $spreadsheet->getActiveSheet()->toArray();

            // Remove header row
            array_shift($rows);

            // Start database transaction
            $db = \Config\Database::connect();
            $db->transBegin();

            $successCount = 0;
            $failedRows = [];

            // Process rows
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // Adding 2 (1 for header, 1 for 0-based index)

                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Transform data if callback is provided
                $data = $dataTransformCallback
                    ? $dataTransformCallback($row, $options)
                    : $this->defaultDataTransform($row);

                try {
                    // Insert data
                    if ($model->insert($data)) {
                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $failedRows[] = [
                        'row' => $rowNumber,
                        'error' => $e->getMessage(),
                        'data' => $data
                    ];
                }
            }

            // Commit or handle partial import
            if (empty($failedRows)) {
                $db->transCommit();
                return [
                    'status' => 'success',
                    'message' => "Berhasil mengimpor {$successCount} data!",
                    'count' => $successCount
                ];
            } else {
                $db->transCommit(); // We commit even with partial failures
                return [
                    'status' => 'partial',
                    'message' => "Berhasil mengimpor {$successCount} data, gagal " . count($failedRows) . " data.",
                    'count' => $successCount,
                    'failed_rows' => $failedRows
                ];
            }
        } catch (\Exception $e) {
            // Rollback on fatal error
            if (isset($db) && $db->transStatus() === false) {
                $db->transRollback();
            }

            // Log error
            log_message('error', 'Excel Import Error: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Gagal memproses file Excel: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Default data transformation if no callback is provided
     * 
     * @param array $row Raw row data
     * @return array Transformed data
     */
    protected function defaultDataTransform(array $row): array
    {
        return $row;
    }

    /**
     * Generate Excel template with headers
     * 
     * @param array $headers Column headers
     * @param string $filename Output filename
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function generateTemplate(array $headers, string $filename = 'template.xlsx')
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set active sheet
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        foreach ($headers as $col => $header) {
            $columnLetter = Coordinate::stringFromColumnIndex($col + 1); // Convert 1-based index to 'A', 'B', etc.
            $cell = $columnLetter . '1'; // Row 1
            $sheet->setCellValue($cell, $header);
        }

        // Create writer
        $writer = new Xlsx($spreadsheet);

        // Send file as download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
