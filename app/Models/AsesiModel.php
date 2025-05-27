<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesiModel extends Model
{
    protected $table            = 'asesi';
    protected $primaryKey       = 'id_asesi';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_asesi',
        'user_id',
        'nik',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'nama_sekolah',
        'jurusan',
        'kebangsaan',
        'telpon_rumah',
        'no_hp',
        'email',
        'pekerjaan',
        'nama_lembaga',
        'jabatan',
        'alamat_perusahaan',
        'email_perusahaan',
        'no_telp_perusahaan',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'kelurahan',
        'rt',
        'rw',
        'kode_pos',
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;

    /**
     * Get all asesi data with user information
     *
     * @param int|null $limit
     * @param int|null $offset
     * @param bool $withUser
     * @return array
     */
    public function getAllAsesi(int $limit = null, int $offset = null, bool $withUser = false)
    {
        if ($withUser) {
            $query = $this->select('asesi.*, users.username, users.fullname as user_fullname')
                ->join('users', 'users.id = asesi.user_id', 'left')
                ->orderBy('asesi.created_at', 'DESC');
        } else {
            $query = $this->orderBy('created_at', 'DESC');
        }

        if ($limit !== null) {
            $query->limit($limit, $offset);
        }

        return $query->findAll();
    }

    /**
     * Get asesi by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getAsesiById(string $id)
    {
        return $this->find($id);
    }

    public function getWithUserByUserId($userId)
    {
        return $this->select('asesi.*, users.username, users.email, users.fullname')
            ->join('users', 'users.id = asesi.user_id')
            ->where('asesi.user_id', $userId)
            ->first();
    }

    public function getAsesiBySkema($idSkema)
    {
        return $this->select('
        a.id_asesi,
        users.fullname as nama_lengkap
    ')
            ->from('asesi a')
            ->join('users', 'users.id = a.user_id')
            ->groupBy('a.id_asesi')
            ->findAll();
    }



    /**
     * Get asesi by user ID
     *
     * @param int $userId
     * @return array|null
     */
    public function getAsesiByUserId(int $userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Get asesi by NIK
     *
     * @param string $nik
     * @return array|null
     */
    public function getAsesiByNik(string $nik)
    {
        return $this->where('nik', $nik)->first();
    }

    /**
     * Get asesi by email
     *
     * @param string $email
     * @return array|null
     */
    public function getAsesiByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Get all pengajuan asesmens for this asesi
     *
     * @param string $asesiId
     * @return array
     */
    public function getPengajuanAsesmen(string $asesiId)
    {
        $pengajuanModel = new PengajuanAsesmenModel();
        return $pengajuanModel->where('id_asesi', $asesiId)->findAll();
    }

    /**
     * Check if NIK already exists
     *
     * @param string $nik
     * @param string|null $excludeId
     * @return bool
     */
    public function isNikExists(string $nik, string $excludeId = null)
    {
        $query = $this->where('nik', $nik);
        if ($excludeId) {
            $query->where('id_asesi !=', $excludeId);
        }
        return $query->countAllResults() > 0;
    }

    /**
     * Check if email already exists
     *
     * @param string $email
     * @param string|null $excludeId
     * @return bool
     */
    public function isEmailExists(string $email, string $excludeId = null)
    {
        $query = $this->where('email', $email);
        if ($excludeId) {
            $query->where('id_asesi !=', $excludeId);
        }
        return $query->countAllResults() > 0;
    }

    /**
     * Get asesi with complete address details
     *
     * @param string $id
     * @return array
     */
    public function getAsesiWithAddressDetails(string $id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('asesi a');
        $builder->select('a.*, p.nama_provinsi, k.nama_kabupaten, kc.nama_kecamatan, kl.nama_kelurahan');
        $builder->join('provinsi p', 'p.id_provinsi = a.provinsi', 'left');
        $builder->join('kabupaten k', 'k.id_kabupaten = a.kabupaten', 'left');
        $builder->join('kecamatan kc', 'kc.id_kecamatan = a.kecamatan', 'left');
        $builder->join('kelurahan kl', 'kl.id_kelurahan = a.kelurahan', 'left');
        $builder->where('a.id_asesi', $id);

        return $builder->get()->getRowArray();
    }
}
