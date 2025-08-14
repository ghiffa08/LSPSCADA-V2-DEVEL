<?php

namespace App\Repositories;

use App\Models\AsesorModel;

/**
 * Asesor Repository
 * 
 * Data access layer for asesor operations
 * 
 * @package App\Repositories
 */
class AsesorRepository
{
    private AsesorModel $model;

    public function __construct()
    {
        $this->model = new AsesorModel();
    }

    /**
     * Find asesor by user ID with skema info
     * 
     * @param int $userId
     * @return array|null
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->model
            ->select('asesor.*, skema.nama_skema, skema.kode_skema, skema.jenis_skema')
            ->join('skema', 'asesor.id_skema = skema.id_skema', 'left')
            ->where('asesor.id_user', $userId)
            ->first();
    }

    /**
     * Find asesor by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Get asesor with skema
     * 
     * @param int $asesorId
     * @return array|null
     */
    public function getWithSkema(int $asesorId): ?array
    {
        return $this->model
            ->select('asesor.*, skema.nama_skema, skema.kode_skema, skema.jenis_skema')
            ->join('skema', 'asesor.id_skema = skema.id_skema', 'left')
            ->where('asesor.id_asesor', $asesorId)
            ->first();
    }

    /**
     * Update asesor data
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Create new asesor
     * 
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        return $this->model->insert($data);
    }

    /**
     * Check if user is asesor
     * 
     * @param int $userId
     * @return bool
     */
    public function isAsesor(int $userId): bool
    {
        return $this->model->where('id_user', $userId)->countAllResults() > 0;
    }

    /**
     * Get active asesors
     */
    public function getActiveAsesors(): array
    {
        try {
            return $this->model
                ->select('id_asesor, nama_lengkap, email, nomor_registrasi')
                ->where('status', 'active')
                ->orderBy('nama_lengkap', 'ASC')
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error getting active asesors: ' . $e->getMessage());
            return [];
        }
    }
}
