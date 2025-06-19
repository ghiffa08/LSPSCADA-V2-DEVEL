<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesorModel extends Model
{
    protected $table = 'asesor';
    protected $primaryKey = 'id_asesor';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_user',
        'nomor_registrasi',
        'bidang_kompetensi'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_user' => 'required|integer|is_unique[asesor.id_user]',
        'bidang_kompetensi' => 'required|max_length[100]',
        'nomor_registrasi' => 'permit_empty|max_length[50]'
    ];

    protected $validationMessages = [
        'id_user' => [
            'required' => 'ID User harus diisi',
            'integer' => 'ID User harus berupa angka',
            'is_unique' => 'User sudah terdaftar sebagai asesor'
        ],
        'bidang_kompetensi' => [
            'required' => 'Bidang kompetensi harus diisi',
            'max_length' => 'Bidang kompetensi maksimal 100 karakter'
        ],
        'nomor_registrasi' => [
            'max_length' => 'Nomor registrasi maksimal 50 karakter'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
    /**
     * Get asesor by user ID
     *
     * @param int $userId
     * @return array|null
     */
    public function getByUserId(int $userId)
    {
        return $this->where('id_user', $userId)->first();
    }

    /**
     * Get asesor with user data (joined)
     *
     * @param string $idAsesor
     * @return array|null
     */
    public function getWithUser(string $idAsesor)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.username, u.nama_lengkap, u.email as user_email, u.active');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->where('a.id_asesor', $idAsesor);

        return $builder->get()->getRowArray();
    }

    /**
     * Get asesor by user ID with user data
     *
     * @param int $userId
     * @return array|null
     */
    public function getByUserIdWithUser(int $userId)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.username, u.nama_lengkap, u.email as user_email, u.active');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->where('a.id_user', $userId);

        return $builder->get()->getRowArray();
    }

    /**
     * Get all asesor with user data
     *
     * @param bool $activeOnly Only return active assessors
     * @return array
     */
    public function getAllWithUser(bool $activeOnly = false)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.username, u.nama_lengkap, u.email as user_email, u.active');
        $builder->join('users u', 'u.id = a.id_user');

        if ($activeOnly) {
            $builder->where('u.active', 1);
        }
        $builder->orderBy('u.nama_lengkap', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Search asesor by name or registration number
     *
     * @param string $search
     * @return array
     */
    public function search(string $search)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.username, u.nama_lengkap, u.email as user_email');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->groupStart()
            ->like('u.nama_lengkap', $search)
            ->orLike('a.nomor_registrasi', $search)
            ->orLike('u.email', $search)
            ->orLike('a.bidang_kompetensi', $search)
            ->groupEnd();

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesor count by bidang kompetensi
     *
     * @return array
     */
    public function getCountByBidangKompetensi()
    {
        $builder = $this->db->table('asesor');
        $builder->select('bidang_kompetensi, COUNT(*) as total');
        $builder->where('bidang_kompetensi IS NOT NULL');
        $builder->groupBy('bidang_kompetensi');
        $builder->orderBy('total', 'DESC');

        return $builder->get()->getResultArray();
    }
}
