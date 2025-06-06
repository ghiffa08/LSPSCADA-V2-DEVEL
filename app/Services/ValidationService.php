<?php

namespace App\Services;

use App\DTOs\ApiResponseDTO;
use CodeIgniter\Validation\Validation;
use CodeIgniter\HTTP\RequestInterface;

/**
 * ValidationService
 * 
 * Centralized validation service for the LSP SCADA application
 * Handles all validation logic for forms, data, and business rules
 */
class ValidationService
{
    private Validation $validation;
    private array $validationRules;
    private array $validationMessages;

    public function __construct()
    {
        $this->validation = \Config\Services::validation();
        $this->initializeValidationRules();
        // $this->initializeValidationMessages(); // Removed, method does not exist
    }

    /**
     * Validate pengajuan asesmen form data
     *
     * @param array $data
     * @return ApiResponseDTO
     */
    public function validatePengajuanAsesmen(array $data): ApiResponseDTO
    {
        $rules = $this->validationRules['pengajuan_asesmen'];

        if (!$this->validation->run($data, $rules)) {
            return ApiResponseDTO::error(
                'Validasi form gagal',
                $this->validation->getErrors(),
                400
            );
        }

        // Additional business logic validation
        $businessValidation = $this->validatePengajuanBusinessRules($data);
        if (!$businessValidation['success']) {
            return ApiResponseDTO::error(
                $businessValidation['message'],
                $businessValidation['errors'],
                400
            );
        }

        return ApiResponseDTO::success('Validasi berhasil');
    }

    /**
     * Validate asesi data
     *
     * @param array $data
     * @return ApiResponseDTO
     */
    public function validateAsesi(array $data): ApiResponseDTO
    {
        $rules = $this->validationRules['asesi'];

        if (!$this->validation->run($data, $rules)) {
            return ApiResponseDTO::error(
                'Validasi asesi gagal',
                $this->validation->getErrors(),
                400
            );
        }

        // Additional asesi-specific validations
        $businessValidation = $this->validateAsesiBusinessRules($data);
        if (!$businessValidation['success']) {
            return ApiResponseDTO::error(
                $businessValidation['message'],
                $businessValidation['errors'],
                400
            );
        }

        return ApiResponseDTO::success('Validasi asesi berhasil');
    }

    /**
     * Validate file uploads
     *
     * @param array $files
     * @return ApiResponseDTO
     */
    public function validateFileUploads(array $files): ApiResponseDTO
    {
        $rules = $this->validationRules['file_uploads'];

        // Example: check for required files
        $missing = [];
        foreach ($rules as $field => $rule) {
            if (!isset($files[$field]) || empty($files[$field]['name'])) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            return ApiResponseDTO::error(
                'File wajib diunggah',
                ['missing_fields' => $missing],
                400
            );
        }

        // Example: check file types and sizes
        foreach ($files as $field => $file) {
            if (!isset($rules[$field])) continue;
            $result = $this->validateSingleFile($file, $rules[$field], $field);
            if (!$result['success']) {
                return ApiResponseDTO::error(
                    $result['message'],
                    $result,
                    400
                );
            }
        }

        return ApiResponseDTO::success('Validasi file upload berhasil');
    }

    /**
     * Validate a single file upload
     *
     * @param object $file
     * @param array $rules
     * @param string $fieldName
     * @return array
     */
    private function validateSingleFile($file, array $rules, string $fieldName): array
    {
        // Check if file is required and exists
        if (isset($rules['required']) && $rules['required']) {
            if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
                return [
                    'success' => false,
                    'error' => "File {$fieldName} wajib diupload"
                ];
            }
        }

        // Skip validation if file is not uploaded and not required
        if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['success' => true];
        }

        // Validate file size
        if (isset($rules['max_size'])) {
            $maxSizeKB = $rules['max_size'];
            $fileSizeKB = $file->getSize() / 1024;

            if ($fileSizeKB > $maxSizeKB) {
                return [
                    'success' => false,
                    'error' => "File {$fieldName} maksimal {$maxSizeKB}KB"
                ];
            }
        }

        // Validate file type
        if (isset($rules['allowed_types'])) {
            $allowedTypes = $rules['allowed_types'];
            $fileMimeType = $file->getMimeType();

            if (!in_array($fileMimeType, $allowedTypes)) {
                $allowedTypesStr = implode(', ', $allowedTypes);
                return [
                    'success' => false,
                    'error' => "File {$fieldName} harus berupa: {$allowedTypesStr}"
                ];
            }
        }

        // Validate file extension
        if (isset($rules['allowed_extensions'])) {
            $allowedExtensions = $rules['allowed_extensions'];
            $fileExtension = $file->getExtension();

            if (!in_array(strtolower($fileExtension), array_map('strtolower', $allowedExtensions))) {
                $allowedExtStr = implode(', ', $allowedExtensions);
                return [
                    'success' => false,
                    'error' => "Ekstensi file {$fieldName} harus: {$allowedExtStr}"
                ];
            }
        }

        return ['success' => true];
    }

    /**
     * Validate pengajuan asesmen business rules
     *
     * @param array $data
     * @return array
     */
    private function validatePengajuanBusinessRules(array $data): array
    {
        $errors = [];

        // Check NIK uniqueness (if provided)
        if (isset($data['nik'])) {
            $asesiModel = new \App\Models\AsesiModel();
            $existingAsesi = $asesiModel->where('nik', $data['nik'])->first();

            if ($existingAsesi) {
                $errors['nik'] = 'NIK sudah terdaftar dalam sistem';
            }
        }

        // Validate age requirements (minimum 17 years old)
        if (isset($data['tanggal_lahir'])) {
            $birthDate = new \DateTime($data['tanggal_lahir']);
            $today = new \DateTime();
            $age = $today->diff($birthDate)->y;

            if ($age < 17) {
                $errors['tanggal_lahir'] = 'Usia minimal 17 tahun untuk mengikuti asesmen';
            }
        }

        // Validate asesmen availability
        if (isset($data['id_asesmen'])) {
            $asesmenModel = new \App\Models\AsesmenModel();
            $asesmen = $asesmenModel->find($data['id_asesmen']);

            if (!$asesmen) {
                $errors['id_asesmen'] = 'Jadwal asesmen tidak ditemukan';
            } else {
                // Check if registration is still open
                $registrationDeadline = new \DateTime($asesmen['registration_deadline'] ?? 'now');
                $today = new \DateTime();

                if ($today > $registrationDeadline) {
                    $errors['id_asesmen'] = 'Pendaftaran untuk jadwal asesmen ini sudah ditutup';
                }
            }
        }

        return [
            'success' => empty($errors),
            'message' => empty($errors) ? 'Validasi bisnis berhasil' : 'Terdapat kesalahan validasi bisnis',
            'errors' => $errors
        ];
    }

    /**
     * Validate asesi business rules
     *
     * @param array $data
     * @return array
     */
    private function validateAsesiBusinessRules(array $data): array
    {
        $errors = [];

        // Validate email uniqueness
        if (isset($data['email'])) {
            $asesiModel = new \App\Models\AsesiModel();
            $existingAsesi = $asesiModel->where('email', $data['email'])->first();

            if ($existingAsesi && $existingAsesi['id_asesi'] !== ($data['id_asesi'] ?? null)) {
                $errors['email'] = 'Email sudah terdaftar dalam sistem';
            }
        }

        // Validate phone number format
        if (isset($data['no_hp'])) {
            if (!preg_match('/^(\+62|62|0)[0-9]{9,12}$/', $data['no_hp'])) {
                $errors['no_hp'] = 'Format nomor HP tidak valid (contoh: 08123456789)';
            }
        }

        // Validate educational background
        if (isset($data['pendidikan_terakhir']) && isset($data['nama_sekolah'])) {
            $validEducationLevels = ['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];

            if (!in_array($data['pendidikan_terakhir'], $validEducationLevels)) {
                $errors['pendidikan_terakhir'] = 'Tingkat pendidikan tidak valid';
            }
        }

        return [
            'success' => empty($errors),
            'message' => empty($errors) ? 'Validasi bisnis asesi berhasil' : 'Terdapat kesalahan validasi bisnis asesi',
            'errors' => $errors
        ];
    }

    /**
     * Validate competency assessment data
     *
     * @param array $data
     * @return ApiResponseDTO
     */
    public function validateCompetencyAssessment(array $data): ApiResponseDTO
    {
        if (!isset($data['assessment_results']) || !is_array($data['assessment_results'])) {
            return ApiResponseDTO::error(
                'Data hasil penilaian tidak valid',
                ['assessment_results' => 'Harus berupa array'],
                400
            );
        }
        // Add more validation as needed
        return ApiResponseDTO::success('Validasi penilaian kompetensi berhasil');
    }

    /**
     * Initialize validation rules
     */
    private function initializeValidationRules(): void
    {
        $this->validationRules = [
            'pengajuan_asesmen' => [
                'nama_siswa' => 'required|max_length[255]',
                'nik' => 'required|numeric|max_length[16]',
                'tempat_lahir' => 'required|max_length[255]',
                'tanggal_lahir' => 'required|valid_date',
                'jenis_kelamin' => 'required|in_list[L,P]',
                'pendidikan_terakhir' => 'required|max_length[50]',
                'nama_sekolah' => 'required|max_length[255]',
                'jurusan' => 'required|max_length[255]',
                'kebangsaan' => 'required|max_length[100]',
                'provinsi' => 'required|numeric',
                'kabupaten' => 'required|numeric',
                'kecamatan' => 'required|numeric',
                'kelurahan' => 'required|numeric',
                'rt' => 'required|max_length[5]',
                'rw' => 'required|max_length[5]',
                'kode_pos' => 'required|max_length[10]',
                'telpon_rumah' => 'permit_empty|numeric',
                'no_hp' => 'required|numeric',
                'email' => 'required|valid_email|max_length[255]',
                'pekerjaan' => 'permit_empty|max_length[255]',
                'nama_lembaga' => 'permit_empty|max_length[255]',
                'jabatan' => 'permit_empty|max_length[255]',
                'alamat_perusahaan' => 'permit_empty|max_length[500]',
                'email_perusahaan' => 'permit_empty|valid_email',
                'no_telp_perusahaan' => 'permit_empty|numeric',
                'id_asesmen' => 'required|numeric'
            ],
            'asesi' => [
                'user_id' => 'required|numeric',
                'nik' => 'required|numeric|max_length[16]',
                'nama' => 'required|max_length[255]',
                'tempat_lahir' => 'required|max_length[255]',
                'tanggal_lahir' => 'required|valid_date',
                'jenis_kelamin' => 'required|in_list[L,P]',
                'pendidikan_terakhir' => 'required|max_length[50]',
                'nama_sekolah' => 'required|max_length[255]',
                'jurusan' => 'required|max_length[255]',
                'kebangsaan' => 'required|max_length[100]',
                'email' => 'required|valid_email|max_length[255]',
                'no_hp' => 'required|numeric'
            ],
            'file_uploads' => [
                'pas_foto' => [
                    'required' => true,
                    'max_size' => 2048, // 2MB in KB
                    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png'],
                    'allowed_extensions' => ['jpg', 'jpeg', 'png']
                ],
                'bukti_pendidikan' => [
                    'required' => true,
                    'max_size' => 5120, // 5MB in KB
                    'allowed_types' => ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'],
                    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png']
                ],
                'file_ktp' => [
                    'required' => true,
                    'max_size' => 2048, // 2MB in KB
                    'allowed_types' => ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'],
                    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png']
                ],
                'raport' => [
                    'required' => true,
                    'max_size' => 5120, // 5MB in KB
                    'allowed_types' => ['application/pdf'],
                    'allowed_extensions' => ['pdf']
                ],
                'sertifikat_pkl' => [
                    'required' => true,
                    'max_size' => 5120, // 5MB in KB
                    'allowed_types' => ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'],
                    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png']
                ]
            ],
            // Legacy support for existing validation
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
    }

    /**
     * Get validation rules by key
     *
     * @param string $key
     * @return array|null
     */
    public function getValidationRules(string $key): ?array
    {
        return $this->validationRules[$key] ?? null;
    }
}
