<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesiModel extends Model
{
    protected $table            = 'asesi';
    protected $primaryKey       = 'id_asesi';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_user',
        'kode_asesi',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'pendidikan_terakhir',
        'nama_sekolah',
        'jurusan',
        'kebangsaan',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'kelurahan',
        'rt',
        'rw',
        'kode_pos',
        'telpon_rumah',
        'pekerjaan',
        'nama_lembaga',
        'jabatan',
        'alamat_perusahaan',
        'email_perusahaan',
        'no_telp_perusahaan',
        'pas_foto',
        'bukti_pendidikan',
        'ktp',
        'tanda_tangan_asesi',
        'raport',
        'sertifikat_pkl',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'id_user' => 'required|integer',
        'nik'     => 'required|exact_length[16]|is_unique[asesi.nik,id_asesi,{id_asesi}]',
    ];
    protected $validationMessages   = [
        'nik' => [
            'is_unique' => 'NIK sudah terdaftar.'
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    public function getByUserId(int $userId)
    {
        return $this->where('id_user', $userId)->first();
    }
}
