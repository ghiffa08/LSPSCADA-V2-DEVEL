<?php

namespace App\Services;

use App\Entities\AsesiEntity;
use App\DTOs\ApiResponseDTO;
use App\Repositories\AsesiRepository;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;

/**
 * AsesiService
 * 
 * Business logic service for asesi (assessee) management
 * Handles CRUD operations and business logic for asesi entities
 */
class AsesiService
{
    protected AsesiRepository $asesiRepository;
    protected BaseConnection $db;

    public function __construct(AsesiRepository $asesiRepository, BaseConnection $db)
    {
        $this->asesiRepository = $asesiRepository;
        $this->db = $db;
    }

    /**
     * Get asesi by ID
     *
     * @param string $id
     * @return ApiResponseDTO
     */
    public function getAsesiById(string $id): ApiResponseDTO
    {
        try {
            $asesi = $this->asesiRepository->findById($id);

            if (!$asesi) {
                return ApiResponseDTO::error('Asesi tidak ditemukan', [], 404);
            }

            return ApiResponseDTO::success('Data asesi berhasil diambil', $asesi);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil data asesi');
        }
    }

    /**
     * Get asesi by user ID
     *
     * @param int $userId
     * @return ApiResponseDTO
     */
    public function getAsesiByUserId(int $userId): ApiResponseDTO
    {
        try {
            $asesi = $this->asesiRepository->findByUserId($userId);

            if (!$asesi) {
                return ApiResponseDTO::error('Asesi tidak ditemukan', [], 404);
            }

            return ApiResponseDTO::success('Data asesi berhasil diambil', $asesi);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi by user ID: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil data asesi');
        }
    }

    /**
     * Create new asesi
     *
     * @param array $data
     * @return ApiResponseDTO
     */
    public function createAsesi(array $data): ApiResponseDTO
    {
        try {
            // Validate required fields
            $validationErrors = $this->validateAsesiData($data);
            if (!empty($validationErrors)) {
                return ApiResponseDTO::validationError($validationErrors);
            }

            // Check for duplicate NIK
            if ($this->asesiRepository->nikExists($data['nik'])) {
                return ApiResponseDTO::error(
                    'NIK sudah terdaftar dalam sistem',
                    ['nik' => 'NIK sudah digunakan']
                );
            }

            // Check for duplicate email
            if ($this->asesiRepository->emailExists($data['email'])) {
                return ApiResponseDTO::error(
                    'Email sudah terdaftar dalam sistem',
                    ['email' => 'Email sudah digunakan']
                );
            }

            // Generate unique ID if not provided
            if (!isset($data['id_asesi'])) {
                $data['id_asesi'] = generate_application_id('ASI', 'asesi', 'id_asesi');
            }

            $data['created_at'] = Time::now();

            if (!$this->asesiRepository->create($data)) {
                throw new \Exception('Gagal menyimpan data asesi');
            }

            return ApiResponseDTO::success(
                'Data asesi berhasil disimpan',
                ['id_asesi' => $data['id_asesi']]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error creating asesi: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat menyimpan data asesi');
        }
    }

    /**
     * Update asesi
     *
     * @param string $id
     * @param array $data
     * @return ApiResponseDTO
     */
    public function updateAsesi(string $id, array $data): ApiResponseDTO
    {
        try {
            // Check if asesi exists
            $existingAsesi = $this->asesiRepository->findById($id);
            if (!$existingAsesi) {
                return ApiResponseDTO::error('Asesi tidak ditemukan', [], 404);
            }

            // Validate data
            $validationErrors = $this->validateAsesiData($data, $id);
            if (!empty($validationErrors)) {
                return ApiResponseDTO::validationError($validationErrors);
            }

            // Check for duplicate NIK (excluding current record)
            if (isset($data['nik']) && $this->asesiRepository->nikExists($data['nik'], $id)) {
                return ApiResponseDTO::error(
                    'NIK sudah terdaftar dalam sistem',
                    ['nik' => 'NIK sudah digunakan']
                );
            }

            // Check for duplicate email (excluding current record)
            if (isset($data['email']) && $this->asesiRepository->emailExists($data['email'], $id)) {
                return ApiResponseDTO::error(
                    'Email sudah terdaftar dalam sistem',
                    ['email' => 'Email sudah digunakan']
                );
            }

            $data['updated_at'] = Time::now();

            if (!$this->asesiRepository->update($id, $data)) {
                throw new \Exception('Gagal mengupdate data asesi');
            }

            return ApiResponseDTO::success('Data asesi berhasil diupdate');
        } catch (\Exception $e) {
            log_message('error', 'Error updating asesi: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengupdate data asesi');
        }
    }

    /**
     * Delete asesi
     *
     * @param string $id
     * @return ApiResponseDTO
     */
    public function deleteAsesi(string $id): ApiResponseDTO
    {
        try {
            // Check if asesi exists
            $asesi = $this->asesiRepository->findById($id);
            if (!$asesi) {
                return ApiResponseDTO::error('Asesi tidak ditemukan', [], 404);
            }

            // TODO: Check if asesi has related pengajuan asesmen before deleting
            // This should be implemented based on business rules

            if (!$this->asesiRepository->delete($id)) {
                throw new \Exception('Gagal menghapus data asesi');
            }

            return ApiResponseDTO::success('Data asesi berhasil dihapus');
        } catch (\Exception $e) {
            log_message('error', 'Error deleting asesi: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat menghapus data asesi');
        }
    }

    /**
     * Search asesi by criteria
     *
     * @param array $criteria
     * @return ApiResponseDTO
     */
    public function searchAsesi(array $criteria): ApiResponseDTO
    {
        try {
            $results = $this->asesiRepository->search($criteria);

            return ApiResponseDTO::success(
                'Pencarian asesi berhasil',
                $results
            );
        } catch (\Exception $e) {
            log_message('error', 'Error searching asesi: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mencari data asesi');
        }
    }

    /**
     * Get asesi statistics
     *
     * @return ApiResponseDTO
     */
    public function getStatistics(): ApiResponseDTO
    {
        try {
            $stats = $this->asesiRepository->getStatistics();

            return ApiResponseDTO::success(
                'Statistik asesi berhasil diambil',
                $stats
            );
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi statistics: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil statistik asesi');
        }
    }

    /**
     * Get DataTables data for asesi
     *
     * @param array $params
     * @return array
     */
    public function getDataTablesData(array $params): array
    {
        try {
            return $this->asesiRepository->getDataTableData($params);
        } catch (\Exception $e) {
            log_message('error', 'Error getting asesi DataTables data: ' . $e->getMessage());
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
     * Validate asesi data
     *
     * @param array $data
     * @param string|null $excludeId
     * @return array
     */
    protected function validateAsesiData(array $data, ?string $excludeId = null): array
    {
        $errors = [];

        // Required fields validation
        $requiredFields = ['nama', 'nik', 'email', 'no_hp', 'jenis_kelamin'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' harus diisi';
            }
        }

        // NIK validation
        if (isset($data['nik']) && !preg_match('/^\d{16}$/', $data['nik'])) {
            $errors['nik'] = 'NIK harus berupa 16 digit angka';
        }

        // Email validation
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid';
        }

        // Phone number validation
        if (isset($data['no_hp']) && !preg_match('/^\d+$/', $data['no_hp'])) {
            $errors['no_hp'] = 'Nomor HP harus berupa angka';
        }

        // Gender validation
        if (isset($data['jenis_kelamin']) && !in_array($data['jenis_kelamin'], AsesiEntity::getValidGenders())) {
            $errors['jenis_kelamin'] = 'Jenis kelamin tidak valid';
        }

        // Education level validation
        if (isset($data['pendidikan_terakhir']) && !in_array($data['pendidikan_terakhir'], AsesiEntity::getValidEducationLevels())) {
            $errors['pendidikan_terakhir'] = 'Pendidikan terakhir tidak valid';
        }

        // Date validation
        if (isset($data['tanggal_lahir'])) {
            try {
                $date = Time::parse($data['tanggal_lahir']);
                if ($date->isAfter(Time::now())) {
                    $errors['tanggal_lahir'] = 'Tanggal lahir tidak boleh di masa depan';
                }
            } catch (\Exception $e) {
                $errors['tanggal_lahir'] = 'Format tanggal lahir tidak valid';
            }
        }

        return $errors;
    }

    /**
     * Get all asesi with pagination
     *
     * @param int $page
     * @param int $perPage
     * @return ApiResponseDTO
     */
    public function getAllAsesiPaginated(int $page = 1, int $perPage = 20): ApiResponseDTO
    {
        try {
            // This would require implementing pagination in the repository
            // For now, we'll use the existing getAll method
            $allAsesi = $this->asesiRepository->getAll();

            // Calculate pagination
            $total = count($allAsesi);
            $offset = ($page - 1) * $perPage;
            $data = array_slice($allAsesi, $offset, $perPage);

            $response = [
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => ceil($total / $perPage)
                ]
            ];

            return ApiResponseDTO::success('Data asesi berhasil diambil', $response);
        } catch (\Exception $e) {
            log_message('error', 'Error getting paginated asesi: ' . $e->getMessage());
            return ApiResponseDTO::error('Terjadi kesalahan saat mengambil data asesi');
        }
    }
}
