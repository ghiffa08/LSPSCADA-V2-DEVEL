<?php

namespace App\Repositories;

use App\Models\PengajuanAsesmenModel;
use App\Entities\PengajuanAsesmenEntity;

/**
 * PengajuanAsesmenRepository
 * 
 * Repository pattern implementation for assessment applications
 * Handles all database operations for pengajuan asesmen
 */
class PengajuanAsesmenRepository
{
    protected PengajuanAsesmenModel $model;

    public function __construct()
    {
        $this->model = new PengajuanAsesmenModel();
    }

    /**
     * Find pengajuan asesmen by ID
     *
     * @param string $id
     * @return PengajuanAsesmenEntity|null
     */
    public function findById(string $id): ?PengajuanAsesmenEntity
    {
        $result = $this->model->find($id);
        return $result ? new PengajuanAsesmenEntity($result) : null;
    }

    /**
     * Find pengajuan asesmen with complete data by ID
     *
     * @param string $id
     * @return array|null
     */
    public function findCompleteById(string $id): ?array
    {
        return $this->model->getCompleteAPL1Data($id);
    }

    /**
     * Find pengajuan asesmen by asesi ID
     *
     * @param string $asesiId
     * @return array
     */
    public function findByAsesiId(string $asesiId): array
    {
        return $this->model->getByAsesiId($asesiId);
    }

    /**
     * Find pengajuan asesmen by status
     *
     * @param string $status
     * @return array
     */
    public function findByStatus(string $status): array
    {
        return $this->model->where('status', $status)
            ->where('deleted_at', null)
            ->findAll();
    }

    /**
     * Create new pengajuan asesmen
     *
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool
    {
        return $this->model->insert($data) !== false;
    }

    /**
     * Update pengajuan asesmen
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
     * Delete pengajuan asesmen (soft delete)
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Count pengajuan asesmen by status
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)
            ->where('deleted_at', null)
            ->countAllResults();
    }

    /**
     * Get pending applications count
     *
     * @return int
     */
    public function getPendingCount(): int
    {
        return $this->countByStatus(PengajuanAsesmenEntity::STATUS_PENDING);
    }

    /**
     * Get approved applications count
     *
     * @return int
     */
    public function getApprovedCount(): int
    {
        return $this->countByStatus(PengajuanAsesmenEntity::STATUS_APPROVED);
    }

    /**
     * Get completed applications count
     *
     * @return int
     */
    public function getCompletedCount(): int
    {
        return $this->countByStatus(PengajuanAsesmenEntity::STATUS_COMPLETED);
    }

    /**
     * Get applications by date range
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getByDateRange(string $startDate, string $endDate): array
    {
        return $this->model->where('created_at >=', $startDate)
            ->where('created_at <=', $endDate)
            ->where('deleted_at', null)
            ->findAll();
    }

    /**
     * Get recent applications
     *
     * @param int $limit
     * @return array
     */
    public function getRecent(int $limit = 10): array
    {
        return $this->model->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Search applications by criteria
     *
     * @param array $criteria
     * @return array
     */
    public function search(array $criteria): array
    {
        $builder = $this->model->builder();

        if (!empty($criteria['status'])) {
            $builder->where('status', $criteria['status']);
        }

        if (!empty($criteria['asesmen_id'])) {
            $builder->where('id_asesmen', $criteria['asesmen_id']);
        }

        if (!empty($criteria['date_from'])) {
            $builder->where('created_at >=', $criteria['date_from']);
        }

        if (!empty($criteria['date_to'])) {
            $builder->where('created_at <=', $criteria['date_to']);
        }

        $builder->where('deleted_at', null);

        return $builder->get()->getResultArray();
    }

    /**
     * Get applications for DataTables with server-side processing
     *
     * @param array $params
     * @return array
     */
    public function getDataTableData(array $params): array
    {
        $builder = $this->model->builder();

        // Join with related tables for complete data
        $builder->select('
            pengajuan_asesmen.*,
            asesi.nama as nama_asesi,
            asesi.email as email_asesi,
            asesmen.tujuan,
            skema.nama_skema,
            tuk.nama_tuk,
            set_tanggal.tanggal
        ');

        $builder->join('asesi', 'asesi.id_asesi = pengajuan_asesmen.id_asesi');
        $builder->join('asesmen', 'asesmen.id_asesmen = pengajuan_asesmen.id_asesmen');
        $builder->join('skema', 'skema.id_skema = asesmen.id_skema', 'left');
        $builder->join('tuk', 'tuk.id_tuk = asesmen.id_tuk', 'left');
        $builder->join('set_tanggal', 'set_tanggal.id_tanggal = asesmen.id_tanggal', 'left');

        $builder->where('pengajuan_asesmen.deleted_at', null);

        // Handle search
        if (!empty($params['search']['value'])) {
            $searchValue = $params['search']['value'];
            $builder->groupStart()
                ->like('asesi.nama', $searchValue)
                ->orLike('asesi.email', $searchValue)
                ->orLike('skema.nama_skema', $searchValue)
                ->orLike('pengajuan_asesmen.status', $searchValue)
                ->groupEnd();
        }

        // Handle ordering
        if (!empty($params['order'][0]['column'])) {
            $orderColumn = $params['columns'][$params['order'][0]['column']]['data'];
            $orderDir = $params['order'][0]['dir'];
            $builder->orderBy($orderColumn, $orderDir);
        } else {
            $builder->orderBy('pengajuan_asesmen.created_at', 'DESC');
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
