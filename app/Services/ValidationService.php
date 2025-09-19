<?php

namespace App\Services;

use CodeIgniter\Validation\Validation;

/**
 * ValidationService
 * Layanan validasi terpusat.
 */
class ValidationService
{
    private Validation $validation;
    private array $validationRules;

    public function __construct()
    {
        $this->validation = \Config\Services::validation();
        $this->initializeValidationRules();
    }

    /**
     * Menjalankan validasi terhadap data yang diberikan.
     */
    public function validateData(array $data, array $rules): bool
    {
        $this->validation->setRules($rules);
        return $this->validation->run($data);
    }

    /**
     * Mengembalikan pesan error validasi.
     */
    public function getErrors(): array
    {
        return $this->validation->getErrors();
    }

    /**
     * Mendapatkan aturan validasi berdasarkan kunci.
     */
    public function getValidationRules(string $key): ?array
    {
        return $this->validationRules[$key] ?? null;
    }

    /**
     * Inisialisasi semua aturan validasi aplikasi.
     */
    private function initializeValidationRules(): void
{
        $this->validationRules = [
            'asesi_profile' => [
                'nama_lengkap'      => 'required|min_length[3]|max_length[100]',
                'no_hp'             => 'required|min_length[10]|max_length[20]',
                'telpon_rumah'      => 'permit_empty|min_length[8]|max_length[20]',
                'nik'               => 'required|exact_length[16]|numeric|is_unique[asesi.nik,id_asesi,{id_asesi}]',
                'tempat_lahir'      => 'required|min_length[2]|max_length[50]',
                'tanggal_lahir'     => 'required|valid_date',
                'jenis_kelamin'     => 'required|in_list[Laki-Laki,Perempuan]',
                'kebangsaan'        => 'required|in_list[WNI,WNA]',
                'provinsi'          => 'required|numeric',
                'kabupaten'         => 'required|numeric',
                'kecamatan'         => 'required|numeric',
                'kelurahan'         => 'required|numeric',
                'rt'                => 'required|min_length[1]|max_length[3]',
                'rw'                => 'required|min_length[1]|max_length[3]',
                'kode_pos'          => 'required|exact_length[5]|numeric',
                'status_pekerjaan'  => 'required|in_list[Pelajar/Mahasiswa,Bekerja,Tidak Bekerja]',
                // Aturan kondisional akan ditambahkan di service
                'nama_sekolah'      => 'permit_empty|max_length[100]',
                'detail_pekerjaan'  => 'permit_empty|max_length[100]',
                'email_perusahaan'  => 'permit_empty|valid_email',
            ],
            'file_uploads' => [
                // MODIFIED: Removed 'permit_empty' to make these fields required.
                'pas_foto'            => 'uploaded[pas_foto]|max_size[pas_foto,2048]|ext_in[pas_foto,png,jpg,jpeg]',
                'tanda_tangan_asesi'  => 'uploaded[tanda_tangan_asesi]|max_size[tanda_tangan_asesi,2048]|ext_in[tanda_tangan_asesi,png,jpg,jpeg]',
                'ktp'                 => 'uploaded[ktp]|max_size[ktp,2048]|ext_in[ktp,png,jpg,jpeg,pdf]',

                // UNCHANGED: These fields remain optional with 'permit_empty'.
                'bukti_pendidikan'    => 'permit_empty|uploaded[bukti_pendidikan]|max_size[bukti_pendidikan,2048]|ext_in[bukti_pendidikan,png,jpg,jpeg,pdf]',
                'raport'              => 'permit_empty|uploaded[raport]|max_size[raport,2048]|ext_in[raport,pdf]',
                'sertifikat_pkl'      => 'permit_empty|uploaded[sertifikat_pkl]|max_size[sertifikat_pkl,2048]|ext_in[sertifikat_pkl,png,jpg,jpeg,pdf]',
            ],
        ];
    }
}
