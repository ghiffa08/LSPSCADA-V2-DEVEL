<?php

namespace App\Services;

class ValidationService
{
    /**
     * Get validation rules by validation type
     * 
     * @param string $type Validation type
     * @return array Validation rules
     */
    public function getValidationRules(string $type): array
    {
        $rules = [
            'apl1_validation' => [
                'validasi_apl1' => [
                    'label' => 'Validasi',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom {field} harus diisi.',
                    ],
                ],
            ],
            'date_validation' => [
                'dateValidated' => [
                    'label' => 'Tanggal Validasi',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Kolom {field} harus diisi.',
                    ],
                ],
            ],
            // Add other rule sets as needed
        ];

        return $rules[$type] ?? [];
    }
}
