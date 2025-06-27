<?php

namespace App\Requests;

use App\Requests\BaseRequest;

/**
 * Create Observasi Request Validation
 * 
 * Handles validation for observasi creation
 * 
 * @package App\Requests
 */
class CreateObservasiRequest extends BaseRequest
{
    /**
     * Validation rules
     * 
     * @var array
     */
    protected $rules = [
        'id_asesmen' => 'required|is_natural_no_zero',
        'id_asesi' => 'permit_empty|max_length[50]',
        'id_pengajuan' => 'permit_empty|is_natural_no_zero',
        'tanggal_observasi' => 'required|valid_date[Y-m-d]',
        'id_tuk' => 'permit_empty|is_natural_no_zero',
        'id_set_tanggal' => 'permit_empty|is_natural_no_zero'
    ];

    /**
     * Custom error messages
     * 
     * @var array
     */
    protected $messages = [
        'id_asesmen' => [
            'required' => 'Asesmen harus dipilih',
            'is_natural_no_zero' => 'ID asesmen tidak valid'
        ],
        'id_asesi' => [
            'max_length' => 'ID asesi maksimal 50 karakter'
        ],
        'id_pengajuan' => [
            'is_natural_no_zero' => 'ID pengajuan tidak valid'
        ],
        'tanggal_observasi' => [
            'required' => 'Tanggal observasi harus diisi',
            'valid_date' => 'Format tanggal tidak valid (gunakan format YYYY-MM-DD)'
        ],
        'id_tuk' => [
            'is_natural_no_zero' => 'ID TUK tidak valid'
        ],
        'id_set_tanggal' => [
            'is_natural_no_zero' => 'ID set tanggal tidak valid'
        ]
    ];

    /**
     * Validate request data
     * 
     * @return bool
     */
    public function validate(): bool
    {
        $this->validation->setRules($this->rules, $this->messages);

        $inputData = $this->getInputData();

        // Custom validation: tanggal observasi tidak boleh masa lalu
        if (!empty($inputData['tanggal_observasi'])) {
            $tanggalObservasi = strtotime($inputData['tanggal_observasi']);
            $today = strtotime(date('Y-m-d'));

            if ($tanggalObservasi < $today) {
                $this->validation->setError('tanggal_observasi', 'Tanggal observasi tidak boleh di masa lalu');
                return false;
            }
        }

        return $this->validation->run($inputData);
    }

    /**
     * Get validated and sanitized data
     * 
     * @return array
     */
    public function getValidatedData(): array
    {
        $inputData = $this->getInputData();
        $validatedData = [];

        foreach (array_keys($this->rules) as $field) {
            if (isset($inputData[$field]) && $inputData[$field] !== '') {
                $validatedData[$field] = $this->sanitizeInput($inputData[$field]);
            }
        }

        // Add default values
        $validatedData['status'] = 'draft';
        $validatedData['created_at'] = date('Y-m-d H:i:s');

        return $validatedData;
    }

    /**
     * Check if request has file uploads
     * 
     * @return bool
     */
    public function hasFiles(): bool
    {
        $files = $this->request->getFiles();
        return !empty($files) && isset($files['dokumen']);
    }

    /**
     * Get uploaded files
     * 
     * @return array
     */
    public function getFiles(): array
    {
        if (!$this->hasFiles()) {
            return [];
        }

        return $this->request->getFiles()['dokumen'] ?? [];
    }
}
