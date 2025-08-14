<?php

namespace App\Repositories;

use App\Models\AsesmenModel;
use App\Repositories\Contracts\AsesmenRepositoryInterface;

/**
 * Asesmen Repository
 * 
 * Data access layer for asesmen operations
 * 
 * @package App\Repositories
 */
class AsesmenRepository implements AsesmenRepositoryInterface
{
    private AsesmenModel $model;

    public function __construct()
    {
        $this->model = new AsesmenModel();
    }

    /**
     * Find asesmen by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Find asesmen by skema ID with JOIN for performance
     * 
     * @param int $skemaId
     * @return array
     */
    public function findBySkemaId(int $skemaId): array
    {
        return $this->model
            ->select('asesmen.id_asesmen, asesmen.tujuan, asesmen.id_skema, 
                     skema.nama_skema, skema.kode_skema')
            ->join('skema', 'asesmen.id_skema = skema.id_skema')
            ->where('asesmen.id_skema', $skemaId)
            ->orderBy('asesmen.id_asesmen', 'ASC')
            ->findAll();
    }

    /**
     * Get all asesmen
     * 
     * @return array
     */
    public function findAll(): array
    {
        return $this->model->findAll();
    }

    /**
     * Create new asesmen
     * 
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        return $this->model->insert($data);
    }

    /**
     * Update asesmen
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
     * Delete asesmen
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }

    /**
     * Get asesmen count by skema
     * 
     * @param int $skemaId
     * @return int
     */
    public function countBySkema(int $skemaId): int
    {
        return $this->model->where('id_skema', $skemaId)->countAllResults();
    }

    /**
     * Find asesmen with skema details
     * 
     * @param array $filters
     * @return array
     */
    public function findWithSkema(array $filters = []): array
    {
        $builder = $this->model
            ->select('asesmen.*, skema.nama_skema, skema.kode_skema')
            ->join('skema', 'asesmen.id_skema = skema.id_skema');

        if (!empty($filters['skema_id'])) {
            $builder->where('asesmen.id_skema', $filters['skema_id']);
        }

        if (!empty($filters['tujuan'])) {
            $builder->like('asesmen.tujuan', $filters['tujuan']);
        }

        return $builder->findAll();
    }

    /**
     * Get available asesmen for specific asesor
     * 
     * @param int $asesorId
     * @return array
     */
    public function getAvailableForAsesor(int $asesorId): array
    {
        return $this->model
            ->select('a.id_asesmen, a.tujuan, s.nama_skema, s.kode_skema, 
                     t.nama_tuk, tg.tanggal_mulai, tg.tanggal_selesai,
                     COUNT(obs.id_observasi) as existing_observasi')
            ->from('asesmen a')
            ->join('skema s', 'a.id_skema = s.id_skema', 'inner')
            ->join('tuk t', 'a.id_tuk = t.id_tuk', 'inner')
            ->join('set_tanggal tg', 'a.id_tanggal = tg.id_tanggal', 'inner')
            ->join('asesor_asesmen aa', 'a.id_asesmen = aa.id_asesmen', 'inner')
            ->join('observasi obs', 'a.id_asesmen = obs.id_asesmen AND obs.id_asesor = ' . $asesorId, 'left')
            ->where('aa.id_asesor', $asesorId)
            ->where('s.status_skema', 'active')
            ->groupBy('a.id_asesmen')
            ->orderBy('tg.tanggal_mulai', 'DESC')
            ->findAll();
    }

    /**
     * Find asesmen by ID (alias for findById for consistency)
     */
    public function find(int $id): ?array
    {
        try {
            return $this->model->find($id);
        } catch (\Exception $e) {
            log_message('error', 'Error finding asesmen: ' . $e->getMessage());
            return null;
        }
    }
}
