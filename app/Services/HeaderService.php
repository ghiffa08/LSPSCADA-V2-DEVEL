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
     * Mengambil konfigurasi header berdasarkan ID asesor.
     * Logikanya: Cari instansi asesor -> cari kop surat instansi -> jika tidak ada, cari kop surat default.
     *
     * @param int|null $assessorId ID dari asesor.
     * @return object Objek berisi data konfigurasi header.
     */
    public function getHeaderForAssessor(?int $assessorId): object
    {
        try {
            $headerConfig = null;

            // 1. Cari instansi dari asesor
            if ($assessorId) {
                $instansiQuery = $this->db->table('asesor_instansi')
                    ->where('asesor_id', $assessorId)
                    ->get()
                    ->getRow();

                // 2. Jika asesor punya instansi, cari header untuk instansi tersebut
                if ($instansiQuery) {
                    $instansiId = $instansiQuery->instansi_id;
                    $headerConfig = $this->db->table('header_konfigurasi')
                        ->where('instansi_id', $instansiId)
                        ->get()
                        ->getRow();
                }
            }

            // 3. Jika header spesifik instansi tidak ditemukan, cari header default global
            if (!$headerConfig) {
                $headerConfig = $this->db->table('header_konfigurasi')
                    ->where('is_active', 1) // is_active = 1 menandakan default
                    ->where('instansi_id IS NULL') // Pastikan ini adalah default global
                    ->get()
                    ->getRow();
            }

            // 4. Jika masih tidak ada, gunakan fallback hardcode
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
        $fallback->logo = 'logolsp.png';
        $fallback->logo_width = 30;
        $fallback->title = 'LEMBAGA SERTIFIKASI PROFESI (FALLBACK)';
        $fallback->header_string = "Alamat Default\nKontak Default";
        return $fallback;
    }
}
