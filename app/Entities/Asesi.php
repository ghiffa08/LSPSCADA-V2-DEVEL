<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;

class Asesi extends Entity
{
    protected $dates = ['created_at', 'updated_at', 'tanggal_lahir'];
    protected $casts = [
        'id_asesi' => 'string',
        'user_id' => 'integer',
        'nik' => 'string',
    ];

    /**
     * Get the User entity associated with this Asesi
     *
     * @return User|null
     */
    public function getUser()
    {
        $userModel = model('UserModel');
        $user = $userModel->find($this->attributes['user_id']);

        if ($user) {
            return new User($user);
        }

        return null;
    }

    /**
     * Get active assessments for this asesi
     *
     * @return array
     */
    public function getActiveAssessments()
    {
        $pengajuanModel = model('PengajuanAsesmenModel');
        return $pengajuanModel->where('id_asesi', $this->attributes['id_asesi'])
                             ->where('status', 'active')
                             ->findAll();
    }

    /**
     * Get all assessments for this asesi
     *
     * @return array
     */
    public function getAllAssessments()
    {
        $pengajuanModel = model('PengajuanAsesmenModel');
        return $pengajuanModel->where('id_asesi', $this->attributes['id_asesi'])
                             ->findAll();
    }

    /**
     * Calculate age based on birth date
     *
     * @return int
     */
    public function getAge()
    {
        if (empty($this->attributes['tanggal_lahir'])) {
            return 0;
        }

        $birthDate = $this->attributes['tanggal_lahir'] instanceof Time
            ? $this->attributes['tanggal_lahir']
            : Time::parse($this->attributes['tanggal_lahir']);

        return $birthDate->difference(Time::now())->getYears();
    }

    /**
     * Get formatted address
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        $parts = [];

        if (!empty($this->attributes['rt'])) {
            $parts[] = "RT " . $this->attributes['rt'];
        }

        if (!empty($this->attributes['rw'])) {
            $parts[] = "RW " . $this->attributes['rw'];
        }

        // Get desa, kecamatan, kabupaten and provinsi names
        if (!empty($this->attributes['kelurahan']) ||
            !empty($this->attributes['kecamatan']) ||
            !empty($this->attributes['kabupaten']) ||
            !empty($this->attributes['provinsi'])) {

            $db = \Config\Database::connect();

            // Get village/kelurahan name
            if (!empty($this->attributes['kelurahan'])) {
                $desa = $db->table('villages')
                          ->where('id', $this->attributes['kelurahan'])
                          ->get()
                          ->getRowArray();

                if ($desa) {
                    $parts[] = $desa['name'];
                }
            }

            // Get subdistrict/kecamatan name
            if (!empty($this->attributes['kecamatan'])) {
                $kecamatan = $db->table('districts')
                              ->where('id', $this->attributes['kecamatan'])
                              ->get()
                              ->getRowArray();

                if ($kecamatan) {
                    $parts[] = "Kecamatan " . $kecamatan['name'];
                }
            }

            // Get city/kabupaten name
            if (!empty($this->attributes['kabupaten'])) {
                $kabupaten = $db->table('regencies')
                              ->where('id', $this->attributes['kabupaten'])
                              ->get()
                              ->getRowArray();

                if ($kabupaten) {
                    $parts[] = $kabupaten['name'];
                }
            }

            // Get province name
            if (!empty($this->attributes['provinsi'])) {
                $provinsi = $db->table('provinces')
                             ->where('id', $this->attributes['provinsi'])
                             ->get()
                             ->getRowArray();

                if ($provinsi) {
                    $parts[] = "Provinsi " . $provinsi['name'];
                }
            }
        }

        if (!empty($this->attributes['kode_pos'])) {
            $parts[] = $this->attributes['kode_pos'];
        }

        return implode(', ', $parts);
    }

    /**
     * Get all certification documents for this asesi
     *
     * @return array
     */
    public function getDocuments()
    {
        // Get all APL1 documents
        $dokumenModel = model('DokumenApl1Model');

        // First get all APL1 IDs for this asesi
        $pengajuanModel = model('PengajuanAsesmenModel');
        $pengajuanList = $pengajuanModel->where('id_asesi', $this->attributes['id_asesi'])
                                      ->findAll();

        $apl1Ids = array_column($pengajuanList, 'id_apl1');

        if (empty($apl1Ids)) {
            return [];
        }

        return $dokumenModel->whereIn('id_apl1', $apl1Ids)->findAll();
    }
}
