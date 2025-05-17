<?php

namespace App\Services;

class QRCodeService
{
    /**
     * Generate QR code
     *
     * @param string $url URL to encode
     * @param string $logo Logo filename
     * @return string Generated QR code
     */
    public function generate(string $url, string $logo = null): string
    {
        // This should contain the same logic as your existing generateQrCode helper
        // You can move the helper logic here or keep using the helper
        return generateQrCode($url, $logo);
    }
}
