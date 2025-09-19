<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesorModel extends Model
{
    protected $table = 'asesor';
    protected $primaryKey = 'id_asesor';
    protected $allowedFields = ['id_user', 'nomor_registrasi', 'id_skema'];

    /**
     * Get asesor by user ID
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->select('asesor.*, users.nama_lengkap, users.email')
            ->join('users', 'users.id = asesor.id_user', 'left')
            ->where('asesor.id_user', $userId)
            ->first();
    }

    /**
     * Get asesor with skema information
     */
    public function getWithSkema(int $asesorId): ?array
    {
        if (!$asesorId) {
            return null;
        }

        return $this->select('
            asesor.id_asesor,
            asesor.nomor_registrasi,
            asesor.id_skema,
            users.nama_lengkap,
            users.email,
            skema.nama_skema,
            skema.kode_skema,
            skema.jenis_skema
        ')
            ->join('users', 'users.id = asesor.id_user', 'left')
            ->join('skema', 'skema.id_skema = asesor.id_skema', 'left')
            ->where('asesor.id_asesor', $asesorId)
            ->first();
    }

    /**
     * [FUNGSI BARU] Get all asesors with their full names from the users table.
     * This is used for populating dropdowns.
     *
     * @return array
     */
    public function findAllAsesorWithUser(): array
    {
        return $this->select('
                        asesor.id_asesor, 
                        users.nama_lengkap as nama_asesor
                    ')
            ->join('users', 'users.id = asesor.id_user', 'left')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();
    }
}
