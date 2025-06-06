<?php

namespace App\Models;

use App\Entities\Asesi;
use CodeIgniter\Model;

class AsesiModel extends Model
{
    protected $table            = 'asesi';
    protected $primaryKey       = 'id_asesi';
    protected $useAutoIncrement = false;
    protected $returnType       = Asesi::class;
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
    protected $validationRules      = [
        'id_asesi' => 'required|max_length[36]',
        'user_id' => 'required|integer',
        'nik' => 'required|max_length[16]|is_unique[asesi.nik,id_asesi,{id_asesi}]',
        'nama' => 'required|max_length[255]',
        'tempat_lahir' => 'required|max_length[255]',
        'tanggal_lahir' => 'required|valid_date',
        'jenis_kelamin' => 'required|in_list[Laki-Laki,Perempuan]',
        'pendidikan_terakhir' => 'required|in_list[SD,SMP,SMA/SMK,Diploma,Sarjana,Magister,Doktor]',
        'email' => 'required|valid_email|is_unique[asesi.email,id_asesi,{id_asesi}]',
    ];

    protected $validationMessages   = [
        'nik' => [
            'required' => 'NIK harus diisi',
            'max_length' => 'NIK maksimal 16 digit',
            'is_unique' => 'NIK sudah digunakan'
        ],
        'email' => [
            'required' => 'Email harus diisi',
            'valid_email' => 'Format email tidak valid',
            'is_unique' => 'Email sudah digunakan'
        ]
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    /**
     * Get asesi data by user ID
     *
     * @param int $userId
     * @return Asesi|null
     */
    public function getByUserId(int $userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Get asesi with user data (joined)
     *
     * @param string $idAsesi
     * @return array|null
     */
    public function getWithUser(string $idAsesi)
    {
        $builder = $this->db->table('asesi a');
        $builder->select('a.*, u.fullname, u.email as user_email, u.active, u.tanda_tangan');
        $builder->join('users u', 'u.id_user = a.id_user');
        $builder->where('a.id_asesi', $idAsesi);

        return $builder->get()->getRowArray();
    }

    /**
     * Get asesi's active assessments
     *
     * @param string $idAsesi
     * @return array
     */
    public function getActiveAssessments(string $idAsesi)
    {
        $builder = $this->db->table('pengajuan_asesmen pa');
        $builder->select('pa.*, a.id_skema, a.id_tuk, a.id_asesor, s.nama_skema');
        $builder->join('asesmen a', 'a.id_asesmen = pa.id_asesmen');
        $builder->join('skema s', 's.id_skema = a.id_skema');
        $builder->where('pa.id_asesi', $idAsesi);
        $builder->where('pa.status', 'active');

        return $builder->get()->getResultArray();
    }

    /**
     * Get asesi with latest assessment status
     *
     * @return array
     */
    public function getAllWithLatestStatus()
    {
        // This query gets all asesi with their latest assessment status using a subquery
        $sql = "SELECT 
                    a.*, 
                    u.fullname, 
                    u.email as user_email,
                    pa.status as assessment_status,
                    s.nama_skema
                FROM 
                    asesi a
                JOIN 
                    users u ON u.id_user = a.id_user
                LEFT JOIN
                    (SELECT 
                        p1.*
                     FROM 
                        pengajuan_asesmen p1
                     JOIN
                        (SELECT 
                            id_asesi, 
                            MAX(created_at) as latest_date
                         FROM 
                            pengajuan_asesmen
                         GROUP BY 
                            id_asesi
                        ) p2 ON p1.id_asesi = p2.id_asesi AND p1.created_at = p2.latest_date
                    ) pa ON pa.id_asesi = a.id_asesi
                LEFT JOIN
                    skema s ON s.id_skema = pa.id_skema
                ORDER BY 
                    a.created_at DESC";

        return $this->db->query($sql)->getResultArray();
    }
}
