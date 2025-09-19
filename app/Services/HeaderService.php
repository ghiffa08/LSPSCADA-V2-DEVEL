<?php

namespace App\Services;

use Exception;
use stdClass;

class HeaderService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Mengambil konfigurasi header untuk asesor tertentu.
     * Jika tidak ada, cari header default. Jika masih tidak ada, gunakan fallback.
     *
     * @param int|null $assessorId ID dari asesor.
     * @return object Objek berisi data konfigurasi.
     */
    public function getHeaderForAssessor(?int $assessorId): object
    {
        try {
            $headerConfig = null;
            $builder = $this->db->table('header_konfigurasi');

            // 1. Coba cari header spesifik untuk asesor
            if ($assessorId) {
                $query = $builder->where('assessor_id', $assessorId)->get();
                $headerConfig = $query->getRow();
            }

            // 2. Jika tidak ada, cari header yang ditandai sebagai default
            if (!$headerConfig) {
                $query = $this->db->table('header_konfigurasi')->where('is_active', 1)->get();
                $headerConfig = $query->getRow();
            }

            // 3. Jika masih belum ada, gunakan fallback
            if (!$headerConfig) {
                return $this->getDefaultHeaderFallback();
            }

            return $headerConfig;
        } catch (Exception $e) {
            log_message('error', 'HeaderService::getHeaderForAssessor Error: ' . $e->getMessage());
            return $this->getDefaultHeaderFallback();
        }
    }

    private function getDefaultHeaderFallback(): object
    {
        $fallback = new stdClass();
        $fallback->logo = 'logolsp.png'; // Logo fallback jika semua gagal
        $fallback->logo_width = 30;
        $fallback->title = 'LEMBAGA SERTIFIKASI PROFESI';
        $fallback->header_string = "Alamat Default";
        return $fallback;
    }
}
