<?php

namespace App\DTOs;

/**
 * PengajuanAsesmenRequestDTO
 * 
 * Data Transfer Object for assessment application requests
 * Handles validation and data transformation for form submissions
 */
class PengajuanAsesmenRequestDTO
{
    // Personal Information
    public string $nama_siswa;
    public string $nik;
    public string $tempat_lahir;
    public string $tanggal_lahir;
    public string $jenis_kelamin;
    public string $pendidikan_terakhir;
    public string $nama_sekolah;
    public string $jurusan;
    public string $kebangsaan;

    // Address Information
    public int $provinsi;
    public int $kabupaten;
    public int $kecamatan;
    public int $kelurahan;
    public string $rt;
    public string $rw;
    public string $kode_pos;

    // Contact Information
    public ?string $telpon_rumah;
    public string $no_hp;
    public string $email;

    // Employment Information
    public string $pekerjaan;
    public ?string $nama_lembaga;
    public ?string $jabatan;
    public ?string $alamat_perusahaan;
    public ?string $email_perusahaan;
    public ?string $no_telp_perusahaan;

    // Assessment Information
    public int $id_asesmen;
    public int $user_id;

    // File uploads (will be handled separately)
    public array $uploaded_files = [];

    /**
     * Create DTO from request data
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();

        // Personal Information
        $dto->nama_siswa = $data['nama_siswa'] ?? '';
        $dto->nik = $data['nik'] ?? '';
        $dto->tempat_lahir = $data['tempat_lahir'] ?? '';
        $dto->tanggal_lahir = $data['tanggal_lahir'] ?? '';
        $dto->jenis_kelamin = $data['jenis_kelamin'] ?? '';
        $dto->pendidikan_terakhir = $data['pendidikan_terakhir'] ?? '';
        $dto->nama_sekolah = $data['nama_sekolah'] ?? '';
        $dto->jurusan = $data['jurusan'] ?? '';
        $dto->kebangsaan = $data['kebangsaan'] ?? '';

        // Address Information
        $dto->provinsi = (int)($data['provinsi'] ?? 0);
        $dto->kabupaten = (int)($data['kabupaten'] ?? 0);
        $dto->kecamatan = (int)($data['kecamatan'] ?? 0);
        $dto->kelurahan = (int)($data['kelurahan'] ?? 0);
        $dto->rt = $data['rt'] ?? '';
        $dto->rw = $data['rw'] ?? '';
        $dto->kode_pos = $data['kode_pos'] ?? '';

        // Contact Information
        $dto->telpon_rumah = !empty($data['telpon_rumah']) ? $data['telpon_rumah'] : null;
        $dto->no_hp = $data['no_hp'] ?? '';
        $dto->email = $data['email'] ?? '';

        // Employment Information
        $dto->pekerjaan = $data['pekerjaan'] ?? '';
        $dto->nama_lembaga = !empty($data['nama_lembaga']) ? $data['nama_lembaga'] : null;
        $dto->jabatan = !empty($data['jabatan']) ? $data['jabatan'] : null;
        $dto->alamat_perusahaan = !empty($data['alamat_perusahaan']) ? $data['alamat_perusahaan'] : null;
        $dto->email_perusahaan = !empty($data['email_perusahaan']) ? $data['email_perusahaan'] : null;
        $dto->no_telp_perusahaan = !empty($data['no_telp_perusahaan']) ? $data['no_telp_perusahaan'] : null;

        // Assessment Information
        $dto->id_asesmen = (int)($data['id_asesmen'] ?? 0);
        $dto->user_id = (int)($data['user_id'] ?? 0);

        return $dto;
    }

    /**
     * Convert to array for database insertion
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'nama_siswa' => $this->nama_siswa,
            'nik' => $this->nik,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'pendidikan_terakhir' => $this->pendidikan_terakhir,
            'nama_sekolah' => $this->nama_sekolah,
            'jurusan' => $this->jurusan,
            'kebangsaan' => $this->kebangsaan,
            'provinsi' => $this->provinsi,
            'kabupaten' => $this->kabupaten,
            'kecamatan' => $this->kecamatan,
            'kelurahan' => $this->kelurahan,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'kode_pos' => $this->kode_pos,
            'telpon_rumah' => $this->telpon_rumah,
            'no_hp' => $this->no_hp,
            'email' => $this->email,
            'pekerjaan' => $this->pekerjaan,
            'nama_lembaga' => $this->nama_lembaga,
            'jabatan' => $this->jabatan,
            'alamat_perusahaan' => $this->alamat_perusahaan,
            'email_perusahaan' => $this->email_perusahaan,
            'no_telp_perusahaan' => $this->no_telp_perusahaan,
            'id_asesmen' => $this->id_asesmen,
            'user_id' => $this->user_id,
        ];
    }

    /**
     * Get asesi data for database insertion
     *
     * @param string $asesiId
     * @return array
     */
    public function getAsesiData(string $asesiId): array
    {
        return [
            'id_asesi' => $asesiId,
            'user_id' => $this->user_id,
            'nik' => $this->nik,
            'nama' => $this->nama_siswa,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir,
            'jenis_kelamin' => $this->jenis_kelamin,
            'pendidikan_terakhir' => $this->pendidikan_terakhir,
            'nama_sekolah' => $this->nama_sekolah,
            'jurusan' => $this->jurusan,
            'kebangsaan' => $this->kebangsaan,
            'provinsi' => $this->provinsi,
            'kabupaten' => $this->kabupaten,
            'kecamatan' => $this->kecamatan,
            'kelurahan' => $this->kelurahan,
            'rt' => $this->rt,
            'rw' => $this->rw,
            'kode_pos' => $this->kode_pos,
            'telpon_rumah' => $this->telpon_rumah,
            'no_hp' => $this->no_hp,
            'email' => $this->email,
            'pekerjaan' => $this->pekerjaan,
            'nama_lembaga' => $this->nama_lembaga,
            'jabatan' => $this->jabatan,
            'alamat_perusahaan' => $this->alamat_perusahaan,
            'email_perusahaan' => $this->email_perusahaan,
            'no_telp_perusahaan' => $this->no_telp_perusahaan,
        ];
    }

    /**
     * Get pengajuan asesmen data for database insertion
     *
     * @param string $apl1Id
     * @param string $asesiId
     * @return array
     */
    public function getPengajuanData(string $apl1Id, string $asesiId): array
    {
        return [
            'id_apl1' => $apl1Id,
            'id_asesi' => $asesiId,
            'id_asesmen' => $this->id_asesmen,
            'status' => 'pending',
        ];
    }

    /**
     * Get document data for database insertion
     *
     * @param string $apl1Id
     * @param array $uploadedFiles
     * @return array
     */
    public function getDokumenData(string $apl1Id, array $uploadedFiles): array
    {
        return [
            'id_apl1' => $apl1Id,
            'pas_foto' => $uploadedFiles['pas_foto'] ?? null,
            'file_ktp' => $uploadedFiles['file_ktp'] ?? null,
            'bukti_pendidikan' => $uploadedFiles['bukti_pendidikan'] ?? null,
            'raport' => $uploadedFiles['raport'] ?? null,
            'sertifikat_pkl' => $uploadedFiles['sertifikat_pkl'] ?? null,
            'tanda_tangan_asesi' => $uploadedFiles['tanda_tangan_asesi'] ?? null,
        ];
    }

    /**
     * Validate the DTO data
     *
     * @return array Array of validation errors, empty if valid
     */
    public function validate(): array
    {
        $errors = [];

        // Required field validation
        if (empty($this->nama_siswa)) {
            $errors['nama_siswa'] = 'Nama siswa harus diisi';
        }

        if (empty($this->nik) || !preg_match('/^\d{16}$/', $this->nik)) {
            $errors['nik'] = 'NIK harus berupa 16 digit angka';
        }

        if (empty($this->email) || !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email harus berupa alamat email yang valid';
        }

        if (empty($this->no_hp) || !preg_match('/^\d+$/', $this->no_hp)) {
            $errors['no_hp'] = 'Nomor HP harus berupa angka';
        }

        if ($this->id_asesmen <= 0) {
            $errors['id_asesmen'] = 'Asesmen harus dipilih';
        }

        if ($this->user_id <= 0) {
            $errors['user_id'] = 'User ID tidak valid';
        }

        return $errors;
    }
}
