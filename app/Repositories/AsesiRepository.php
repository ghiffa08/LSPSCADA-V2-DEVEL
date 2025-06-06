<?php

namespace App\Repositories;

use App\Models\AsesiModel;
use App\Entities\AsesiEntity;

/**
 * AsesiRepository
 * 
 * Repository pattern implementation for asesi (assessee) entities
 * Handles all database operations for asesi
 */
class AsesiRepository
{
    protected AsesiModel $model;

    public function __construct()
    {
        $this->model = new AsesiModel();
    }

    /**
     * Find asesi by ID
     *
     * @param string $id
     * @return AsesiEntity|null
     */
    public function findById(string $id): ?AsesiEntity
    {
        $result = $this->model->find($id);
        return $result ? new AsesiEntity($result) : null;
    }

    /**
     * Find asesi by user ID
     *
     * @param int $userId
     * @return AsesiEntity|null
     */
    public function findByUserId(int $userId): ?AsesiEntity
    {
        $result = $this->model->where('user_id', $userId)->first();
        return $result ? new AsesiEntity($result) : null;
    }

    /**
     * Find asesi by NIK
     *
     * @param string $nik
     * @return AsesiEntity|null
     */
    public function findByNIK(string $nik): ?AsesiEntity
    {
        $result = $this->model->where('nik', $nik)->first();
        return $result ? new AsesiEntity($result) : null;
    }

    /**
     * Find asesi by email
     *
     * @param string $email
     * @return AsesiEntity|null
     */
    public function findByEmail(string $email): ?AsesiEntity
    {
        $result = $this->model->where('email', $email)->first();
        return $result ? new AsesiEntity($result) : null;
    }

    /**
     * Create new asesi
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        return $this->model->insert($data) !== false;
    }

    /**
     * Update asesi
     *
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function update(string $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Delete asesi
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Check if NIK exists (for uniqueness validation)
     *
     * @param string $nik
     * @param string|null $excludeId
     * @return bool
     */
    public function nikExists(string $nik, ?string $excludeId = null): bool
    {
        $builder = $this->model->builder();
        $builder->where('nik', $nik);

        if ($excludeId) {
            $builder->where('id_asesi !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Check if email exists (for uniqueness validation)
     *
     * @param string $email
     * @param string|null $excludeId
     * @return bool
     */
    public function emailExists(string $email, ?string $excludeId = null): bool
    {
        $builder = $this->model->builder();
        $builder->where('email', $email);

        if ($excludeId) {
            $builder->where('id_asesi !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Get all asesi
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->model->findAll();
    }

    /**
     * Get asesi by gender
     *
     * @param string $gender
     * @return array
     */
    public function getByGender(string $gender): array
    {
        return $this->model->where('jenis_kelamin', $gender)->findAll();
    }

    /**
     * Get asesi by education level
     *
     * @param string $education
     * @return array
     */
    public function getByEducation(string $education): array
    {
        return $this->model->where('pendidikan_terakhir', $education)->findAll();
    }

    /**
     * Get asesi by province
     *
     * @param int $provinceId
     * @return array
     */
    public function getByProvince(int $provinceId): array
    {
        return $this->model->where('provinsi', $provinceId)->findAll();
    }

    /**
     * Search asesi by criteria
     *
     * @param array $criteria
     * @return array
     */
    public function search(array $criteria): array
    {
        $builder = $this->model->builder();

        if (!empty($criteria['nama'])) {
            $builder->like('nama', $criteria['nama']);
        }

        if (!empty($criteria['nik'])) {
            $builder->like('nik', $criteria['nik']);
        }

        if (!empty($criteria['email'])) {
            $builder->like('email', $criteria['email']);
        }

        if (!empty($criteria['jenis_kelamin'])) {
            $builder->where('jenis_kelamin', $criteria['jenis_kelamin']);
        }

        if (!empty($criteria['pendidikan_terakhir'])) {
            $builder->where('pendidikan_terakhir', $criteria['pendidikan_terakhir']);
        }

        if (!empty($criteria['provinsi'])) {
            $builder->where('provinsi', $criteria['provinsi']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesi statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $total = $this->model->countAllResults();

        $genderStats = $this->model->select('jenis_kelamin, COUNT(*) as count')
            ->groupBy('jenis_kelamin')
            ->get()
            ->getResultArray();

        $educationStats = $this->model->select('pendidikan_terakhir, COUNT(*) as count')
            ->groupBy('pendidikan_terakhir')
            ->get()
            ->getResultArray();

        return [
            'total' => $total,
            'by_gender' => $genderStats,
            'by_education' => $educationStats
        ];
    }

    /**
     * Get asesi for DataTables with server-side processing
     *
     * @param array $params
     * @return array
     */
    public function getDataTableData(array $params): array
    {
        $builder = $this->model->builder();

        // Join with related tables for complete data
        $builder->select('
            asesi.*,
            provinsi.nama as nama_provinsi,
            kabupaten.nama as nama_kabupaten,
            kecamatan.nama as nama_kecamatan,
            kelurahan.nama as nama_kelurahan
        ');

        $builder->join('provinsi', 'provinsi.id = asesi.provinsi', 'left');
        $builder->join('kabupaten', 'kabupaten.id = asesi.kabupaten', 'left');
        $builder->join('kecamatan', 'kecamatan.id = asesi.kecamatan', 'left');
        $builder->join('kelurahan', 'kelurahan.id = asesi.kelurahan', 'left');

        // Handle search
        if (!empty($params['search']['value'])) {
            $searchValue = $params['search']['value'];
            $builder->groupStart()
                ->like('asesi.nama', $searchValue)
                ->orLike('asesi.nik', $searchValue)
                ->orLike('asesi.email', $searchValue)
                ->orLike('asesi.no_hp', $searchValue)
                ->groupEnd();
        }

        // Handle ordering
        if (!empty($params['order'][0]['column'])) {
            $orderColumn = $params['columns'][$params['order'][0]['column']]['data'];
            $orderDir = $params['order'][0]['dir'];
            $builder->orderBy($orderColumn, $orderDir);
        } else {
            $builder->orderBy('asesi.created_at', 'DESC');
        }

        // Count total records
        $totalRecords = $builder->countAllResults(false);

        // Handle pagination
        if (isset($params['start']) && isset($params['length'])) {
            $builder->limit($params['length'], $params['start']);
        }

        $data = $builder->get()->getResultArray();

        return [
            'draw' => intval($params['draw'] ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];
    }
}
