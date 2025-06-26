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
        'id_skema'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id_user' => 'required|integer|is_unique[asesor.id_user,id_asesor,{id_asesor}]',
        'nomor_registrasi' => 'permit_empty|max_length[50]',
        'id_skema' => 'permit_empty|integer|is_not_unique[skema.id_skema]'
    ];

    protected $validationMessages = [
        'id_user' => [
            'required' => 'ID User harus diisi',
            'integer' => 'ID User harus berupa angka',
            'is_unique' => 'User sudah terdaftar sebagai asesor'
        ],
        'nomor_registrasi' => [
            'max_length' => 'Nomor registrasi maksimal 50 karakter'
        ],
        'id_skema' => [
            'integer' => 'ID Skema harus berupa angka',
            'is_not_unique' => 'Skema tidak ditemukan'
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
        $builder->join('asesor_skema ask', 'ask.id_asesor = a.id_asesor', 'left');
        $builder->join('skema s', 's.id_skema = ask.id_skema', 'left');
        $builder->groupStart()
            ->like('u.nama_lengkap', $search)
            ->orLike('a.nomor_registrasi', $search)
            ->orLike('u.email', $search)
            ->orLike('s.nama_skema', $search)
            ->groupEnd();
        $builder->groupBy('a.id_asesor');

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesor count by skema
     *
     * @return array
     */
    public function getCountBySkema()
    {
        $builder = $this->db->table('asesor_skema as ask');
        $builder->select('s.nama_skema, COUNT(*) as total');
        $builder->join('skema s', 's.id_skema = ask.id_skema');
        $builder->groupBy('s.nama_skema');
        $builder->orderBy('total', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesor with competencies using many-to-many relationship
     *
     * @param int|null $id_asesor Optional asesor ID to filter by
     * @return array
     */
    public function getAsesorWithSkema($id_asesor = null)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.username, u.nama_lengkap, u.email as user_email, u.active, 
                         GROUP_CONCAT(s.nama_skema SEPARATOR ", ") as bidang_kompetensi,
                         GROUP_CONCAT(s.id_skema) as skema_ids');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->join('asesor_skema ask', 'ask.id_asesor = a.id_asesor', 'left');
        $builder->join('skema s', 's.id_skema = ask.id_skema', 'left');
        $builder->groupBy('a.id_asesor');

        if ($id_asesor !== null) {
            $builder->where('a.id_asesor', $id_asesor);
            return $builder->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesor by skema competency
     * 
     * @param int $id_skema Skema ID
     * @return array Array of asesor data
     */
    public function getAsesorBySkema($id_skema)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.nama_lengkap, u.email');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->join('asesor_skema ask', 'ask.id_asesor = a.id_asesor');
        $builder->where('ask.id_skema', $id_skema);
        $builder->where('u.active', 1);

        return $builder->get()->getResultArray();
    }

    /**
     * Save asesor with competencies
     */
    public function saveAsesorWithSkema($data, $skemaIds = [])
    {
        $this->db->transBegin();

        try {
            // Save/update asesor basic data
            $id_asesor = $data['id_asesor'] ?? null;
            unset($data['id_asesor']); // Remove from data array

            if ($id_asesor) {
                $this->update($id_asesor, $data);
            } else {
                $id_asesor = $this->insert($data, true);
            }

            // Update skema relationships
            // First delete existing relationships
            $this->db->table('asesor_skema')->where('id_asesor', $id_asesor)->delete();

            // Insert new relationships
            if (!empty($skemaIds)) {
                $batchData = [];
                foreach ($skemaIds as $id_skema) {
                    $batchData[] = [
                        'id_asesor' => $id_asesor,
                        'id_skema' => $id_skema
                    ];
                }
                $this->db->table('asesor_skema')->insertBatch($batchData);
            }

            $this->db->transCommit();
            return $id_asesor;
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Get asesor skema competencies
     */
    public function getAsesorSkemas($id_asesor)
    {
        $builder = $this->db->table('asesor_skema ask');
        $builder->select('s.id_skema, s.nama_skema, s.kode_skema');
        $builder->join('skema s', 's.id_skema = ask.id_skema');
        $builder->where('ask.id_asesor', $id_asesor);

        return $builder->get()->getResultArray();
    }

    /**
     * Get skema assigned to an asesor
     *
     * @param int $id_asesor Asesor ID
     * @return array Array of skema IDs
     */
    public function getAsesorSkemaIds(int $id_asesor): array
    {
        $builder = $this->db->table('asesor_skema');
        $builder->select('id_skema');
        $builder->where('id_asesor', $id_asesor);
        $result = $builder->get()->getResultArray();

        return array_map(function ($item) {
            return $item['id_skema'];
        }, $result);
    }

    /**
     * Update asesor's skema assignment (one-to-one)
     *
     * @param int $id_asesor Asesor ID
     * @param int|null $skema_id Skema ID to assign (null to remove assignment)
     * @return bool Success status
     */
    public function updateAsesorSkema(int $id_asesor, $skema_id = null): bool
    {
        try {
            // Log what we're doing
            log_message('debug', 'AsesorModel::updateAsesorSkema - Updating skema for asesor #' . $id_asesor .
                ' with skema ID: ' . json_encode($skema_id));

            // Validate skema ID if provided
            if ($skema_id !== null) {
                $skema_id = filter_var($skema_id, FILTER_VALIDATE_INT);
                if ($skema_id === false) {
                    log_message('error', 'AsesorModel::updateAsesorSkema - Invalid skema ID');
                    return false;
                }
            }

            // Update the asesor record with the new skema
            $result = $this->update($id_asesor, ['id_skema' => $skema_id]);

            log_message('debug', 'AsesorModel::updateAsesorSkema - Update result: ' . ($result ? 'Success' : 'Failed'));

            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error updating asesor skema: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get asesor with skema data (one-to-one)
     *
     * @param int|null $id_asesor
     * @return array|null
     */
    public function getWithSkema($id_asesor = null)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.nama_lengkap, u.email, u.username, s.nama_skema, s.kode_skema, s.jenis_skema');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->join('skema s', 's.id_skema = a.id_skema', 'left');

        if ($id_asesor !== null) {
            $builder->where('a.id_asesor', $id_asesor);
            return $builder->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get all asesors with their user and skema data
     *
     * @param bool $activeOnly
     * @return array
     */
    public function getAllWithUserAndSkema(bool $activeOnly = false)
    {
        $builder = $this->db->table('asesor a');
        $builder->select('a.*, u.username, u.nama_lengkap, u.email as user_email, u.active, s.nama_skema, s.kode_skema');
        $builder->join('users u', 'u.id = a.id_user');
        $builder->join('skema s', 's.id_skema = a.id_skema', 'left');

        if ($activeOnly) {
            $builder->where('u.active', 1);
        }

        $builder->orderBy('u.nama_lengkap', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Check if asesor has specific skema assigned
     *
     * @param int $id_asesor
     * @param int $id_skema
     * @return bool
     */
    public function hasSkema(int $id_asesor, int $id_skema): bool
    {
        $asesor = $this->find($id_asesor);
        return $asesor && $asesor['id_skema'] == $id_skema;
    }

    /**
     * Get asesors by skema ID
     *
     * @param int $id_skema
     * @return array
     */
    public function getBySkema(int $id_skema): array
    {
        return $this->where('id_skema', $id_skema)->findAll();
    }
}
