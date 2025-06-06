<?php

namespace App\Services;

use App\DTOs\PengajuanAsesmenRequestDTO;
use App\DTOs\ApiResponseDTO;
use App\Repositories\PengajuanAsesmenRepository;
use App\Repositories\AsesiRepository;
use App\Models\DokumenApl1Model;
use App\Services\FileUploadInterface;
use App\Services\EmailService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;

/**
 * PengajuanAsesmenService
 * 
 * Business logic service for assessment applications
 * Handles the complete workflow of pengajuan asesmen
 */
class PengajuanAsesmenService
{
    protected PengajuanAsesmenRepository $pengajuanRepository;
    protected AsesiRepository $asesiRepository;
    protected DokumenApl1Model $dokumenModel;
    protected FileUploadInterface $fileUploadService;
    protected EmailService $emailService;
    protected BaseConnection $db;

    public function __construct(
        PengajuanAsesmenRepository $pengajuanRepository,
        AsesiRepository $asesiRepository,
        DokumenApl1Model $dokumenModel,
        FileUploadInterface $fileUploadService,
        EmailService $emailService,
        BaseConnection $db
    ) {
        $this->pengajuanRepository = $pengajuanRepository;
        $this->asesiRepository = $asesiRepository;
        $this->dokumenModel = $dokumenModel;
        $this->fileUploadService = $fileUploadService;
        $this->emailService = $emailService;
        $this->db = $db;
    }

    /**
     * Submit a new assessment application
     *
     * @param PengajuanAsesmenRequestDTO $dto
     * @param array $uploadedFiles
     * @return ApiResponseDTO
     */
    public function submitApplication(PengajuanAsesmenRequestDTO $dto, array $uploadedFiles): ApiResponseDTO
    {
        // Validate DTO
        $validationErrors = $dto->validate();
        if (!empty($validationErrors)) {
            return ApiResponseDTO::validationError($validationErrors);
        }

        // Check for duplicate NIK
        if ($this->asesiRepository->nikExists($dto->nik)) {
            return ApiResponseDTO::error('NIK sudah terdaftar dalam sistem', ['nik' => 'NIK sudah digunakan']);
        }

        // Check for duplicate email
        if ($this->asesiRepository->emailExists($dto->email)) {
            return ApiResponseDTO::error('Email sudah terdaftar dalam sistem', ['email' => 'Email sudah digunakan']);
        }

        // Begin database transaction
        $this->db->transBegin();

        try {
            // Process file uploads
            $fileUploadResult = $this->processFileUploads($uploadedFiles);
            if (!$fileUploadResult['success']) {
                throw new \Exception($fileUploadResult['error']);
            }

            // Generate unique IDs
            $idAsesi = $this->generateUniqueId('ASI', 'asesi', 'id_asesi');
            $idApl1 = $this->generateUniqueId('APL01', 'pengajuan_asesmen', 'id_apl1');

            // Create asesi record
            $asesiData = $dto->getAsesiData($idAsesi);
            $asesiData['created_at'] = Time::now();

            if (!$this->asesiRepository->create($asesiData)) {
                throw new \Exception('Gagal menyimpan data asesi');
            }

            // Create pengajuan asesmen record
            $pengajuanData = $dto->getPengajuanData($idApl1, $idAsesi);
            $pengajuanData['created_at'] = Time::now();

            if (!$this->pengajuanRepository->create($pengajuanData)) {
                throw new \Exception('Gagal menyimpan data pengajuan asesmen');
            }

            // Create dokumen record
            $dokumenData = $dto->getDokumenData($idApl1, $fileUploadResult['files']);
            $dokumenData['created_at'] = Time::now();

            if (!$this->dokumenModel->insert($dokumenData)) {
                throw new \Exception('Gagal menyimpan data dokumen');
            }

            // Commit transaction
            $this->db->transCommit();

            // Send notification email (async if queue is available)
            $this->sendNotificationEmail($dto->email, $dto->nama_siswa, $idApl1);

            return ApiResponseDTO::success(
                'Pengajuan uji kompetensi berhasil dikirim. Silakan cek email untuk informasi lebih lanjut.',
                ['application_id' => $idApl1]
            );
        } catch (\Exception $e) {
            // Rollback transaction
            $this->db->transRollback();

            // Clean up uploaded files if any
            if (isset($fileUploadResult['files'])) {
                $this->cleanupUploadedFiles($fileUploadResult['files']);
            }

            log_message('error', 'Error submitting assessment application: ' . $e->getMessage());

            return ApiResponseDTO::error(
                'Terjadi kesalahan saat memproses pengajuan: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get application by ID with complete data
     *
     * @param string $id
     * @return ApiResponseDTO
     */
    public function getApplicationById(string $id): ApiResponseDTO
    {
        try {
            $application = $this->pengajuanRepository->findCompleteById($id);

            if (!$application) {
                return ApiResponseDTO::error('Pengajuan asesmen tidak ditemukan', [], 404);
            }

            return ApiResponseDTO::success('Data pengajuan asesmen berhasil diambil', $application);
        } catch (\Exception $e) {
            log_message('error', 'Error getting application: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil data pengajuan');
        }
    }

    /**
     * Update application status
     *
     * @param string $id
     * @param string $status
     * @param string $validator
     * @param string|null $email
     * @return ApiResponseDTO
     */
    public function updateApplicationStatus(string $id, string $status, string $validator, ?string $email = null): ApiResponseDTO
    {
        try {
            $application = $this->pengajuanRepository->findById($id);

            if (!$application) {
                return ApiResponseDTO::error('Pengajuan asesmen tidak ditemukan', [], 404);
            }

            $updateData = [
                'status' => $status,
                'validator' => $validator,
                'email_validasi' => $email,
                'updated_at' => Time::now()
            ];

            if (!$this->pengajuanRepository->update($id, $updateData)) {
                throw new \Exception('Gagal mengupdate status pengajuan');
            }

            // Send status update notification
            $completeData = $this->pengajuanRepository->findCompleteById($id);
            if ($completeData && isset($completeData['asesi']['email'])) {
                $this->sendStatusUpdateEmail($completeData, $status);
            }

            return ApiResponseDTO::success('Status pengajuan berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error updating application status: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengupdate status pengajuan');
        }
    }

    /**
     * Get applications by criteria
     *
     * @param array $criteria
     * @return ApiResponseDTO
     */
    public function getApplicationsByCriteria(array $criteria): ApiResponseDTO
    {
        try {
            $applications = $this->pengajuanRepository->search($criteria);

            return ApiResponseDTO::success(
                'Data pengajuan asesmen berhasil diambil',
                $applications
            );
        } catch (\Exception $e) {
            log_message('error', 'Error searching applications: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mencari data pengajuan');
        }
    }

    /**
     * Get DataTables data for applications
     *
     * @param array $params
     * @return array
     */
    public function getDataTablesData(array $params): array
    {
        try {
            return $this->pengajuanRepository->getDataTableData($params);
        } catch (\Exception $e) {
            log_message('error', 'Error getting DataTables data: ' . $e->getMessage());
            return [
                'draw' => intval($params['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data'
            ];
        }
    }

    /**
     * Get application statistics
     *
     * @return ApiResponseDTO
     */
    public function getStatistics(): ApiResponseDTO
    {
        try {
            $stats = [
                'pending' => $this->pengajuanRepository->getPendingCount(),
                'approved' => $this->pengajuanRepository->getApprovedCount(),
                'completed' => $this->pengajuanRepository->getCompletedCount(),
                'recent' => $this->pengajuanRepository->getRecent(5)
            ];

            return ApiResponseDTO::success('Statistik pengajuan asesmen berhasil diambil', $stats);
        } catch (\Exception $e) {
            log_message('error', 'Error getting statistics: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil statistik');
        }
    }

    /**
     * Process file uploads
     *
     * @param array $uploadedFiles
     * @return array
     */
    protected function processFileUploads(array $uploadedFiles): array
    {
        $fileConfigs = [
            'pas_foto' => [
                'directory' => 'asesi/photo',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png'],
                'max_size' => 1024 // 1MB
            ],
            'file_ktp' => [
                'directory' => 'asesi/ktp',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 2048 // 2MB
            ],
            'bukti_pendidikan' => [
                'directory' => 'asesi/education',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 5120 // 5MB
            ],
            'raport' => [
                'directory' => 'asesi/report',
                'allowed_types' => ['application/pdf'],
                'max_size' => 5120 // 5MB
            ],
            'sertifikat_pkl' => [
                'directory' => 'asesi/certificate',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png', 'application/pdf'],
                'max_size' => 5120 // 5MB
            ],
            'tanda_tangan_asesi' => [
                'directory' => 'asesi/signature',
                'allowed_types' => ['image/jpg', 'image/jpeg', 'image/png'],
                'max_size' => 1024 // 1MB
            ]
        ];

        $results = $this->fileUploadService->uploadMultipleFiles($uploadedFiles, $fileConfigs);

        $successFiles = [];
        foreach ($results as $field => $result) {
            if (!$result['success']) {
                return [
                    'success' => false,
                    'error' => "Error uploading {$field}: " . $result['error']
                ];
            }
            $successFiles[$field] = $result['filename'];
        }

        return [
            'success' => true,
            'files' => $successFiles
        ];
    }

    /**
     * Generate unique ID for entities
     *
     * @param string $prefix
     * @param string $table
     * @param string $field
     * @return string
     */
    protected function generateUniqueId(string $prefix, string $table, string $field): string
    {
        return generate_application_id($prefix, $table, $field);
    }

    /**
     * Send notification email
     *
     * @param string $email
     * @param string $name
     * @param string $applicationId
     * @return void
     */
    protected function sendNotificationEmail(string $email, string $name, string $applicationId): void
    {
        try {
            $this->emailService->sendEmail(
                $email,
                'Pendaftaran Uji Kompetensi Keahlian',
                'email/email_message',
                [
                    'name' => $name,
                    'id' => $applicationId
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error sending notification email: ' . $e->getMessage());
        }
    }

    /**
     * Send status update email
     *
     * @param array $applicationData
     * @param string $status
     * @return void
     */
    protected function sendStatusUpdateEmail(array $applicationData, string $status): void
    {
        try {
            $statusMessages = [
                'approved' => 'Pengajuan Anda telah disetujui',
                'rejected' => 'Pengajuan Anda ditolak',
                'completed' => 'Pengajuan Anda telah selesai diproses'
            ];

            $message = $statusMessages[$status] ?? 'Status pengajuan Anda telah diupdate';

            $this->emailService->sendEmail(
                $applicationData['asesi']['email'],
                'Update Status Pengajuan Uji Kompetensi',
                'email/status_update',
                [
                    'name' => $applicationData['asesi']['nama'],
                    'status' => $message,
                    'application_id' => $applicationData['pengajuan']['id_apl1']
                ]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error sending status update email: ' . $e->getMessage());
        }
    }

    /**
     * Clean up uploaded files
     *
     * @param array $files
     * @return void
     */
    protected function cleanupUploadedFiles(array $files): void
    {
        foreach ($files as $file) {
            $fullPath = WRITEPATH . 'uploads/' . $file;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
