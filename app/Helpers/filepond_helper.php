<?php

if (!function_exists('initFilePond')) {
    /**
     * Generate script inisialisasi FilePond secara global.
     *
     * @param string $selector Selector elemen input (default '.filepond-upload')
     * @param array $options Konfigurasi tambahan FilePond (opsional)
     * @return string Script HTML untuk FilePond
     */
    function initFilePond(string $selector = '.filepond-upload', array $options = []): string
    {
        // Default konfigurasi FilePond
        $defaultOptions = [
            'credits' => null,
            'allowImagePreview' => true,
            'allowImageFilter' => true,
            'allowImageExifOrientation' => false,
            'allowImageCrop' => false,
            'imageFilterColorMatrix' => [
                0.299,
                0.587,
                0.114,
                0,
                0,
                0.299,
                0.587,
                0.114,
                0,
                0,
                0.299,
                0.587,
                0.114,
                0,
                0,
                0.0,
                0.0,
                0.0,
                1,
                0
            ],
            'acceptedFileTypes' => ["image/png", "image/jpg", "image/jpeg"],
            'fileValidateTypeDetectType' => 'function(source, type) { return new Promise(resolve => resolve(type)); }',
            'storeAsFile' => true,
        ];

        // Merge default + custom options
        $mergedOptions = array_replace_recursive($defaultOptions, $options);

        // Convert PHP array ke JSON
        $jsonOptions = json_encode($mergedOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Karena ada fungsi JS (fileValidateTypeDetectType), replace kutipnya manual
        $jsonOptions = str_replace('"function(', 'function(', $jsonOptions);
        $jsonOptions = str_replace('})"', '})', $jsonOptions);

        // Return HTML script
        return <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function () {
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginImageCrop,
            FilePondPluginImageExifOrientation,
            FilePondPluginImageFilter,
            FilePondPluginImageResize,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType
        );

        const filePondOptions = {$jsonOptions};

        document.querySelectorAll('{$selector}').forEach(element => {
            FilePond.create(element, filePondOptions);
        });
    });
</script>
HTML;
    }
}
