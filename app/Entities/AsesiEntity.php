<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

/**
 * Asesi Entity
 * 
 * Represents the asesi (assessee) entity with proper typing and business logic
 */
class AsesiEntity extends Entity
{
    protected $datamap = [
        'id'      => 'id_asesi',
        'user_id' => 'user_id'
    ];

    protected $dates = ['tanggal_lahir', 'created_at', 'updated_at'];

    protected $casts = [
        'id_asesi'             => 'string',
        'user_id'              => 'integer',
        'nik'                  => 'string',
        'nama'                 => 'string',
        'tempat_lahir'         => 'string',
        'tanggal_lahir'        => 'datetime',
        'jenis_kelamin'        => 'string',
        'pendidikan_terakhir'  => 'string',
        'nama_sekolah'         => 'string',
        'jurusan'              => 'string',
        'kebangsaan'           => 'string',
        'provinsi'             => 'integer',
        'kabupaten'            => 'integer',
        'kecamatan'            => 'integer',
        'kelurahan'            => 'integer',
        'rt'                   => 'string',
        'rw'                   => 'string',
        'kode_pos'             => 'string',
        'telpon_rumah'         => '?string',
        'no_hp'                => 'string',
        'email'                => 'string',
        'pekerjaan'            => 'string',
        'nama_lembaga'         => '?string',
        'jabatan'              => '?string',
        'alamat_perusahaan'    => '?string',
        'email_perusahaan'     => '?string',
        'no_telp_perusahaan'   => '?string',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime'
    ];

    // Gender constants
    public const GENDER_MALE = 'Laki-laki';
    public const GENDER_FEMALE = 'Perempuan';

    // Education level constants
    public const EDUCATION_SD = 'SD';
    public const EDUCATION_SMP = 'SMP';
    public const EDUCATION_SMA = 'SMA/SMK';
    public const EDUCATION_D3 = 'D3';
    public const EDUCATION_S1 = 'S1';
    public const EDUCATION_S2 = 'S2';
    public const EDUCATION_S3 = 'S3';

    /**
     * Get all valid gender options
     *
     * @return array
     */
    public static function getValidGenders(): array
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE
        ];
    }

    /**
     * Get all valid education levels
     *
     * @return array
     */
    public static function getValidEducationLevels(): array
    {
        return [
            self::EDUCATION_SD,
            self::EDUCATION_SMP,
            self::EDUCATION_SMA,
            self::EDUCATION_D3,
            self::EDUCATION_S1,
            self::EDUCATION_S2,
            self::EDUCATION_S3
        ];
    }

    /**
     * Get full name with proper formatting
     *
     * @return string
     */
    public function getFullName(): string
    {
        return trim($this->nama);
    }

    /**
     * Get formatted address
     *
     * @return string
     */
    public function getFormattedAddress(): string
    {
        $addressParts = array_filter([
            "RT {$this->rt}",
            "RW {$this->rw}",
            $this->kode_pos ? "Kode Pos: {$this->kode_pos}" : null
        ]);

        return implode(', ', $addressParts);
    }

    /**
     * Get age in years
     *
     * @return int|null
     */
    public function getAge(): ?int
    {
        if (!$this->tanggal_lahir) {
            return null;
        }

        return $this->tanggal_lahir->diff(Time::now())->y;
    }

    /**
     * Check if asesi is male
     *
     * @return bool
     */
    public function isMale(): bool
    {
        return $this->jenis_kelamin === self::GENDER_MALE;
    }

    /**
     * Check if asesi is female
     *
     * @return bool
     */
    public function isFemale(): bool
    {
        return $this->jenis_kelamin === self::GENDER_FEMALE;
    }

    /**
     * Get contact information
     *
     * @return array
     */
    public function getContactInfo(): array
    {
        return [
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'telpon_rumah' => $this->telpon_rumah
        ];
    }

    /**
     * Get employment information
     *
     * @return array
     */
    public function getEmploymentInfo(): array
    {
        return [
            'pekerjaan' => $this->pekerjaan,
            'nama_lembaga' => $this->nama_lembaga,
            'jabatan' => $this->jabatan,
            'alamat_perusahaan' => $this->alamat_perusahaan,
            'email_perusahaan' => $this->email_perusahaan,
            'no_telp_perusahaan' => $this->no_telp_perusahaan
        ];
    }

    /**
     * Validate NIK format (Indonesian National ID)
     *
     * @return bool
     */
    public function isValidNIK(): bool
    {
        return preg_match('/^\d{16}$/', $this->nik);
    }

    /**
     * Get birth place and date formatted
     *
     * @return string
     */
    public function getBirthInfo(): string
    {
        if (!$this->tanggal_lahir) {
            return $this->tempat_lahir;
        }

        return sprintf(
            '%s, %s',
            $this->tempat_lahir,
            $this->tanggal_lahir->toDateString()
        );
    }
}
