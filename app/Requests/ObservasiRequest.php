<?php

namespace App\Requests;

use CodeIgniter\Validation\StrictRules\Rules;

class ObservasiRequest
{
    protected $validation;

    public function __construct()
    {
        $this->validation = \Config\Services::validation();
    }

    /**
     * Validate data for loading observasi
     */
    public function validateLoad(array $data): array
    {
        $rules = [
            'id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID Skema wajib diisi',
                    'integer' => 'ID Skema harus berupa angka',
                    'greater_than' => 'ID Skema harus lebih dari 0'
                ]
            ],
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID Asesmen wajib diisi',
                    'integer' => 'ID Asesmen harus berupa angka',
                    'greater_than' => 'ID Asesmen harus lebih dari 0'
                ]
            ],
            'id_asesi' => [
                'label' => 'ID Asesi',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID Asesi wajib diisi',
                    'integer' => 'ID Asesi harus berupa angka',
                    'greater_than' => 'ID Asesi harus lebih dari 0'
                ]
            ]
        ];

        return $this->runValidation($rules, $data);
    }

    /**
     * Validate data for saving observasi
     */
    public function validateSave(array $data): array
    {
        $baseRules = [
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID Asesmen wajib diisi',
                    'integer' => 'ID Asesmen harus berupa angka',
                    'greater_than' => 'ID Asesmen harus lebih dari 0'
                ]
            ],
            'id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID Skema wajib diisi',
                    'integer' => 'ID Skema harus berupa angka',
                    'greater_than' => 'ID Skema harus lebih dari 0'
                ]
            ],
            'id_asesi' => [
                'label' => 'ID Asesi',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID Asesi wajib diisi',
                    'integer' => 'ID Asesi harus berupa angka',
                    'greater_than' => 'ID Asesi harus lebih dari 0'
                ]
            ],
            'tanggal_observasi' => [
                'label' => 'Tanggal Observasi',
                'rules' => 'required|valid_date[Y-m-d]',
                'errors' => [
                    'required' => 'Tanggal Observasi wajib diisi',
                    'valid_date' => 'Format tanggal harus YYYY-MM-DD'
                ]
            ]
        ];

        // Add specific rules based on save type
        $rules = $this->addSaveTypeRules($baseRules, $data);

        $validation = $this->runValidation($rules, $data);

        // Additional custom validation for complex data
        if ($validation['valid']) {
            $customValidation = $this->runCustomValidation($data);
            if (!$customValidation['valid']) {
                return $customValidation;
            }
        }

        return $validation;
    }

    /**
     * Validate batch save request
     */
    public function validateBatch(array $data): array
    {
        $rules = [
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_asesi' => [
                'label' => 'ID Asesi',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'tanggal_observasi' => [
                'label' => 'Tanggal Observasi',
                'rules' => 'required|valid_date[Y-m-d]'
            ],
            'items' => [
                'label' => 'Items Data',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Data items wajib diisi'
                ]
            ]
        ];

        $validation = $this->runValidation($rules, $data);

        if ($validation['valid'] && isset($data['items'])) {
            $itemValidation = $this->validateBatchItems($data['items']);
            if (!empty($itemValidation)) {
                return [
                    'valid' => false,
                    'errors' => $itemValidation
                ];
            }
        }

        return $validation;
    }

    /**
     * Validate single KUK save request
     */
    public function validateSingleKuk(array $data): array
    {
        $rules = [
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_asesi' => [
                'label' => 'ID Asesi',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_kuk' => [
                'label' => 'ID KUK',
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'ID KUK wajib diisi',
                    'integer' => 'ID KUK harus berupa angka',
                    'greater_than' => 'ID KUK harus lebih dari 0'
                ]
            ],
            'kompeten' => [
                'label' => 'Status Kompeten',
                'rules' => 'required|in_list[Y,N]',
                'errors' => [
                    'required' => 'Status kompeten wajib diisi',
                    'in_list' => 'Status kompeten harus Y atau N'
                ]
            ],
            'keterangan' => [
                'label' => 'Keterangan',
                'rules' => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Keterangan maksimal 500 karakter'
                ]
            ],
            'tanggal_observasi' => [
                'label' => 'Tanggal Observasi',
                'rules' => 'required|valid_date[Y-m-d]'
            ]
        ];

        return $this->runValidation($rules, $data);
    }

    /**
     * Validate delete request
     */
    public function validateDelete(array $data): array
    {
        $rules = [
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_asesi' => [
                'label' => 'ID Asesi',
                'rules' => 'required|integer|greater_than[0]'
            ]
        ];

        return $this->runValidation($rules, $data);
    }

    /**
     * Validate progress request
     */
    public function validateProgress(array $data): array
    {
        $rules = [
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'required|integer|greater_than[0]'
            ],
            'id_asesi' => [
                'label' => 'ID Asesi',
                'rules' => 'permit_empty|integer|greater_than[0]'
            ]
        ];

        return $this->runValidation($rules, $data);
    }

    /**
     * Validate statistics request
     */
    public function validateStatistics(array $data): array
    {
        $rules = [
            'id_asesmen' => [
                'label' => 'ID Asesmen',
                'rules' => 'permit_empty|integer|greater_than[0]'
            ],
            'id_skema' => [
                'label' => 'ID Skema',
                'rules' => 'permit_empty|integer|greater_than[0]'
            ],
            'date_from' => [
                'label' => 'Tanggal Mulai',
                'rules' => 'permit_empty|valid_date[Y-m-d]'
            ],
            'date_to' => [
                'label' => 'Tanggal Akhir',
                'rules' => 'permit_empty|valid_date[Y-m-d]'
            ]
        ];

        return $this->runValidation($rules, $data);
    }

    /**
     * Add specific validation rules based on save type
     */
    protected function addSaveTypeRules(array $baseRules, array $data): array
    {
        if (!isset($data['save_type'])) {
            return $baseRules;
        }

        switch ($data['save_type']) {
            case 'kuk':
                $baseRules['id_kuk'] = [
                    'label' => 'ID KUK',
                    'rules' => 'required|integer|greater_than[0]',
                    'errors' => [
                        'required' => 'ID KUK wajib diisi',
                        'integer' => 'ID KUK harus berupa angka',
                        'greater_than' => 'ID KUK harus lebih dari 0'
                    ]
                ];
                $baseRules['kompeten'] = [
                    'label' => 'Status Kompeten',
                    'rules' => 'required|in_list[Y,N]',
                    'errors' => [
                        'required' => 'Status kompeten wajib diisi',
                        'in_list' => 'Status kompeten harus Y atau N'
                    ]
                ];
                $baseRules['keterangan'] = [
                    'label' => 'Keterangan',
                    'rules' => 'permit_empty|max_length[500]',
                    'errors' => [
                        'max_length' => 'Keterangan maksimal 500 karakter'
                    ]
                ];
                break;

            case 'batch':
                $baseRules['items'] = [
                    'label' => 'Items Data',
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Data items wajib diisi'
                    ]
                ];
                break;

            case 'settings':
                // Only basic fields required for settings
                unset($baseRules['tanggal_observasi']);
                break;
        }

        return $baseRules;
    }

    /**
     * Run custom validation for complex scenarios
     */
    protected function runCustomValidation(array $data): array
    {
        $errors = [];

        // Validate date is not in the future
        if (isset($data['tanggal_observasi'])) {
            $observasiDate = strtotime($data['tanggal_observasi']);
            $today = strtotime(date('Y-m-d'));

            if ($observasiDate > $today) {
                $errors['tanggal_observasi'] = 'Tanggal observasi tidak boleh di masa depan';
            }
        }

        // Validate batch items if present
        if (isset($data['save_type']) && $data['save_type'] === 'batch' && isset($data['items'])) {
            $itemErrors = $this->validateBatchItems($data['items']);
            if (!empty($itemErrors)) {
                $errors = array_merge($errors, $itemErrors);
            }
        }

        // Validate foreign key relationships could be checked here
        // but we'll leave that to the service layer for better separation

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Run validation with given rules and data
     */
    protected function runValidation(array $rules, array $data): array
    {
        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            return [
                'valid' => false,
                'errors' => $this->validation->getErrors()
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate batch items structure and content
     */
    protected function validateBatchItems($items): array
    {
        $errors = [];

        // Check if items is an array or object
        if (!is_array($items) && !is_object($items)) {
            $errors['items'] = 'Items harus berupa array atau object';
            return $errors;
        }

        // Convert to array if object
        if (is_object($items)) {
            $items = (array) $items;
        }

        // Check if items is empty
        if (empty($items)) {
            $errors['items'] = 'Items tidak boleh kosong';
            return $errors;
        }

        // Validate each item
        foreach ($items as $kukId => $item) {
            $itemPrefix = "items[{$kukId}]";

            // Validate KUK ID
            if (!is_numeric($kukId) || $kukId <= 0) {
                $errors["{$itemPrefix}.id"] = 'ID KUK harus berupa angka positif';
                continue;
            }

            // Check if item is array/object
            if (!is_array($item) && !is_object($item)) {
                $errors["{$itemPrefix}"] = 'Item harus berupa array atau object';
                continue;
            }

            // Convert to array if object
            if (is_object($item)) {
                $item = (array) $item;
            }

            // Validate kompeten field
            if (!isset($item['kompeten'])) {
                $errors["{$itemPrefix}.kompeten"] = 'Status kompeten wajib diisi';
            } elseif (!in_array($item['kompeten'], ['Y', 'N'])) {
                $errors["{$itemPrefix}.kompeten"] = 'Status kompeten harus Y atau N';
            }

            // Validate keterangan field
            if (isset($item['keterangan'])) {
                if (!is_string($item['keterangan'])) {
                    $errors["{$itemPrefix}.keterangan"] = 'Keterangan harus berupa text';
                } elseif (strlen($item['keterangan']) > 500) {
                    $errors["{$itemPrefix}.keterangan"] = 'Keterangan maksimal 500 karakter';
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitize input data
     */
    public function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Basic sanitization
                $sanitized[$key] = trim(strip_tags($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRF(array $data): bool
    {
        $request = \Config\Services::request();
        $security = \Config\Services::security();

        // Get CSRF token from data or headers
        $token = $data[csrf_token()] ?? $request->getHeaderLine('X-CSRF-TOKEN') ?? null;

        if (!$token) {
            return false;
        }

        return $security->CSRFVerify($token);
    }

    /**
     * Check rate limiting (basic implementation)
     */
    public function checkRateLimit(string $identifier, int $maxRequests = 100, int $timeWindow = 3600): bool
    {
        $cache = \Config\Services::cache();
        $key = "rate_limit_{$identifier}";

        $requests = $cache->get($key) ?? [];
        $now = time();

        // Remove old requests outside time window
        $requests = array_filter($requests, function ($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });

        // Check if limit exceeded
        if (count($requests) >= $maxRequests) {
            return false;
        }

        // Add current request
        $requests[] = $now;
        $cache->save($key, $requests, $timeWindow);

        return true;
    }

    // Legacy methods for backward compatibility
    public function getRules(): array
    {
        return [
            'id_asesor' => 'required|integer',
            'id_asesi' => 'required|max_length[50]',
            'id_pengajuan' => 'required|integer',
            'tanggal_observasi' => 'required|valid_date[Y-m-d]',
            'details' => 'required|is_array',
            'details.*.id_skema' => 'required|integer',
            'details.*.id_kuk' => 'required|integer',
            'details.*.kompeten' => 'required|in_list[Y,N]',
            'details.*.keterangan' => 'permit_empty|string|max_length[1000]'
        ];
    }

    public function getMessages(): array
    {
        return [
            'id_asesor' => [
                'required' => 'ID Asesor harus diisi',
                'integer' => 'ID Asesor harus berupa angka'
            ],
            'id_asesi' => [
                'required' => 'ID Asesi harus diisi',
                'max_length' => 'ID Asesi maksimal 50 karakter'
            ],
            'id_pengajuan' => [
                'required' => 'ID Pengajuan harus diisi',
                'integer' => 'ID Pengajuan harus berupa angka'
            ],
            'tanggal_observasi' => [
                'required' => 'Tanggal observasi harus diisi',
                'valid_date' => 'Format tanggal observasi tidak valid (gunakan Y-m-d)'
            ],
            'details' => [
                'required' => 'Detail observasi harus diisi',
                'is_array' => 'Detail observasi harus berupa array'
            ],
            'details.*.id_skema' => [
                'required' => 'ID Skema pada detail harus diisi',
                'integer' => 'ID Skema harus berupa angka'
            ],
            'details.*.id_kuk' => [
                'required' => 'ID KUK pada detail harus diisi',
                'integer' => 'ID KUK harus berupa angka'
            ],
            'details.*.kompeten' => [
                'required' => 'Status kompeten harus diisi',
                'in_list' => 'Status kompeten harus Y atau N'
            ],
            'details.*.keterangan' => [
                'max_length' => 'Keterangan maksimal 1000 karakter'
            ]
        ];
    }

    /**
     * Legacy validate method
     */
    public function validate(array $data): array
    {
        $validation = \Config\Services::validation();
        $validation->setRules($this->getRules(), $this->getMessages());

        if (!$validation->run($data)) {
            throw new \InvalidArgumentException(implode(', ', $validation->getErrors()));
        }

        return $this->sanitizeLegacy($data);
    }

    /**
     * Legacy sanitize method
     */
    private function sanitizeLegacy(array $data): array
    {
        // Sanitize main observation data
        $sanitized = [
            'id_asesor' => (int) $data['id_asesor'],
            'id_asesi' => trim($data['id_asesi']),
            'id_pengajuan' => (int) $data['id_pengajuan'],
            'tanggal_observasi' => $data['tanggal_observasi']
        ];

        // Sanitize details
        $sanitized['details'] = [];
        foreach ($data['details'] as $detail) {
            $sanitized['details'][] = [
                'id_skema' => (int) $detail['id_skema'],
                'id_kuk' => (int) $detail['id_kuk'],
                'kompeten' => strtoupper(trim($detail['kompeten'])),
                'keterangan' => isset($detail['keterangan']) ? trim($detail['keterangan']) : '',
                'tanggal_observasi' => $data['tanggal_observasi'] // Inherit from main
            ];
        }

        return $sanitized;
    }
}
