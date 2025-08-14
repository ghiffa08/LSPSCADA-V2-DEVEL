<?php

namespace App\Repositories;

use App\Models\ObservasiModel;
use App\Repositories\Contracts\ObservasiRepositoryInterface;

/**
 * Observasi Repository
 * 
 * Data access layer for observasi operations
 * 
 * @package App\Repositories
 */
class ObservasiRepository implements ObservasiRepositoryInterface
{
    private ObservasiModel $model;

    public function __construct()
    {
        $this->model = new ObservasiModel();
    }

    /**
     * Find observasi by ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        return $this->model->find($id);
    }

    /**
     * Find observasi by ID and asesor ID (for security)
     * 
     * @param int $id
     * @param int $asesorId
     * @return array|null
     */
    public function findByIdAndAsesor(int $id, int $asesorId): ?array
    {
        return $this->model
            ->where('id_observasi', $id)
            ->where('id_asesor', $asesorId)
            ->first();
    }

    /**
     * Get observasi by asesor with filters
     * 
     * @param int $asesorId
     * @param array $filters
     * @return array
     */
    public function findByAsesor(int $asesorId, array $filters = []): array
    {
        $builder = $this->model->where('id_asesor', $asesorId);

        if (!empty($filters['tanggal_mulai'])) {
            $builder->where('tanggal_observasi >=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $builder->where('tanggal_observasi <=', $filters['tanggal_selesai']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->orderBy('created_at', 'DESC')->findAll();
    }

    /**
     * Get observasi with full details
     * 
     * @param int $id
     * @return array|null
     */
    public function getWithDetails(int $id): ?array
    {
        return $this->model
            ->select('observasi.*, asesmen.tujuan as asesmen_tujuan, 
                     skema.nama_skema, skema.kode_skema,
                     asesor.nomor_registrasi')
            ->join('asesmen', 'observasi.id_asesmen = asesmen.id_asesmen')
            ->join('skema', 'asesmen.id_skema = skema.id_skema')
            ->join('asesor', 'observasi.id_asesor = asesor.id_asesor')
            ->where('observasi.id_observasi', $id)
            ->first();
    }

    /**
     * Get observasi by asesmen ID
     */
    public function getByAsesmenId(int $asesmenId): array
    {
        try {
            return $this->model
                ->where('id_asesmen', $asesmenId)
                ->orderBy('created_at', 'DESC')
                ->findAll();
        } catch (\Exception $e) {
            log_message('error', 'Error in getByAsesmenId: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get observasi criteria/questions
     */
    public function getObservasiCriteria(): array
    {
        try {
            // For now, return default criteria since the criteria table might not exist
            return [
                [
                    'id' => 1,
                    'kategori' => 'Persiapan',
                    'pertanyaan' => 'Apakah asesor telah mempersiapkan tempat asesmen?',
                    'bobot' => 1
                ],
                [
                    'id' => 2,
                    'kategori' => 'Pelaksanaan',
                    'pertanyaan' => 'Apakah asesor melakukan asesmen sesuai skema?',
                    'bobot' => 2
                ],
                [
                    'id' => 3,
                    'kategori' => 'Penutup',
                    'pertanyaan' => 'Apakah asesor memberikan feedback yang konstruktif?',
                    'bobot' => 1
                ]
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in getObservasiCriteria: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get observasi summary
     */
    public function getObservasiSummary(int $asesorId = null): array
    {
        try {
            $builder = $this->model->builder();

            if ($asesorId) {
                $builder->where('id_asesor', $asesorId);
            }

            $total = $builder->countAllResults(false);
            $completed = $builder->where('status', 'completed')->countAllResults(false);
            $pending = $builder->where('status', 'draft')->countAllResults();

            return [
                'total' => $total,
                'completed' => $completed,
                'pending' => $pending,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in getObservasiSummary: ' . $e->getMessage());
            return [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'completion_rate' => 0
            ];
        }
    }

    /**
     * Create new observasi
     */
    public function create(array $data): int
    {
        try {
            return $this->model->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Error creating observasi: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update observasi
     */
    public function update(int $id, array $data): bool
    {
        try {
            return $this->model->update($id, $data);
        } catch (\Exception $e) {
            log_message('error', 'Error updating observasi: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete observasi
     */
    public function delete(int $id): bool
    {
        try {
            return $this->model->delete($id);
        } catch (\Exception $e) {
            log_message('error', 'Error deleting observasi: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find observasi by ID
     */
    public function find(int $id): ?array
    {
        try {
            return $this->model->find($id);
        } catch (\Exception $e) {
            log_message('error', 'Error finding observasi: ' . $e->getMessage());
            return null;
        }
    }
}
