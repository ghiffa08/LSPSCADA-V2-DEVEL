<?php

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;


if (!function_exists('generateQrCode')) {
    /**
     * Generate a QR Code with optional logo
     *
     * @param string $url URL or text to encode
     * @param string|null $logoPath Path to logo image (optional)
     * @param int $size Size of the QR code
     * @param int $margin Margin around the QR code
     * @return string Data URI of the generated QR code
     */
    function generateQrCode(string $url, string $logoPath = null, int $size = 200, int $margin = 10): string
    {
        $writer = new PngWriter();

        $qrCode = \Endroid\QrCode\QrCode::create($url)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize($size)
            ->setMargin($margin)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $logo = null;
        if ($logoPath && file_exists($logoPath)) {
            $logo = Logo::create($logoPath)
                ->setResizeToWidth(50);
        }

        $result = $writer->write($qrCode, $logo);

        return $result->getDataUri();
    }
}
