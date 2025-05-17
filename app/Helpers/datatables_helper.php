<?php

if (!function_exists('initDataTable')) {
    /**
     * Generate script inisialisasi DataTables.
     *
     * @param string|null $selector Selector tabel (ex: '#table-1' atau '.datatable'), default global ke '.datatable'
     * @param array $options Tambahan konfigurasi DataTables (opsional)
     * @return string HTML script tag
     */
    function initDataTable(string $selector = null, array $options = []): string
    {
        $selector = $selector ?? '.datatable'; // default ke semua table class 'datatable'

        $defaultOptions = [
            'responsive'    => true,
            'processing'    => true,
            'serverSide'    => false, // default false supaya tetap support data statis
            'lengthChange'  => true,
            'autoWidth'     => false,
            'pageLength'    => 10,
            'language' => [
                'search'         => 'Cari:',
                'lengthMenu'     => 'Tampilkan _MENU_ data per halaman',
                'zeroRecords'    => 'Data tidak ditemukan',
                'info'           => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                'infoEmpty'      => 'Tidak ada data',
                'infoFiltered'   => '(disaring dari _MAX_ total data)',
                'paginate' => [
                    'first'    => 'Awal',
                    'last'     => 'Akhir',
                    'next'     => 'Selanjutnya',
                    'previous' => 'Sebelumnya',
                ],
            ],
        ];

        $mergedOptions = array_replace_recursive($defaultOptions, $options);
        $jsonOptions = json_encode($mergedOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<HTML
<script>
$(function() {
    $('{$selector}').DataTable({$jsonOptions});
});
</script>
HTML;
    }
}
