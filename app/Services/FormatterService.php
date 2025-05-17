<?php

namespace App\Services;

class FormatterService
{
    /**
     * Format gender display
     *
     * @param string $jenisKelamin
     * @return string
     */
    public function formatJenisKelamin(string $jenisKelamin): string
    {
        // Move the formatting logic from your helper here
        return formatJenisKelamin($jenisKelamin);
    }

    /**
     * Format certification type display
     *
     * @param string $jenisSertifikasi
     * @return string
     */
    public function formatJenisSertifikasi(string $jenisSertifikasi): string
    {
        return formatJenisSertifikasi($jenisSertifikasi);
    }

    /**
     * Format assessment purpose display
     *
     * @param string $tujuan
     * @return string
     */
    public function formatTujuanAsesmen(string $tujuan): string
    {
        return formatTujuanAsesmen($tujuan);
    }

    /**
     * Format status display
     *
     * @param string $status
     * @return string
     */
    public function formatStatus(string $status): string
    {
        return formatStatus($status);
    }

    /**
     * Format basic evidence display
     *
     * @param string|null $pasFoto
     * @return string
     */
    public function formatBuktiDasar(?string $pasFoto): string
    {
        return formatBuktiDasar($pasFoto);
    }
}
