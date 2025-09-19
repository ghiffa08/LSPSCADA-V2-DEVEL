<?= $this->extend("layouts/admin/layout-admin"); ?>

<?= $this->section("content"); ?>
<h2 class="section-title">Monitoring Ceklis Observasi</h2>
<p class="section-lead">Halaman ini menampilkan semua data observasi yang telah dilaksanakan.</p>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>Data Observasi</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-observasi" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Asesi</th>
                                <th>Nama Asesor</th>
                                <th>Skema Sertifikasi</th>
                                <th>TUK</th>
                                <th>Tanggal Observasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?php // Section "modals" tidak diperlukan karena halaman ini read-only 
?>

<?= $this->section('js') ?>
<script>
    $(document).ready(function() {
        // Konfigurasi dasar
        const baseUrl = '<?= base_url() ?>';

        // Inisialisasi DataTable
        $('#table-observasi').DataTable({
            "processing": true,
            "serverSide": true,
            "responsive": true,
            "order": [],
            "ajax": {
                // Pastikan URL ini sesuai dengan API Controller Anda
                "url": `${baseUrl}/admin/observasi/get-data-table`,
                "type": "POST"
            },
            "columns": [{
                    "data": null,
                    "orderable": false
                },
                {
                    "data": "nama_asesi"
                },
                {
                    "data": "nama_asesor"
                },
                {
                    "data": "nama_skema"
                },
                {
                    "data": "nama_tuk"
                },
                {
                    "data": "tanggal_observasi"
                },
                {
                    "data": null,
                    "orderable": false
                }
            ],
            "columnDefs": [{
                // Kolom nomor urut
                "targets": 0,
                "render": (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1
            }, {
                // Format tanggal menjadi format Indonesia
                "targets": 5,
                "render": (data) => new Date(data).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                })
            }, {
                // Kolom Aksi hanya berisi tombol PDF/Print
                "targets": -1,
                "render": (data, type, row) => `
                    <button class="btn btn-sm btn-primary btn-print" data-id="${row.id_observasi}" title="Cetak PDF">
                        <i class="fas fa-print"></i> PDF
                    </button>`
            }],
        });

        /**
         * Menangani aksi cetak PDF.
         * @param {string|number} id - ID observasi yang akan dicetak.
         */
        function printObservasi(id) {
            Swal.fire({
                title: 'Mempersiapkan Dokumen',
                text: 'Mohon tunggu...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Pastikan URL ini sesuai dengan route untuk generate PDF Anda
            const printUrl = `${baseUrl}/pdf/observasi/${id}`;
            const printWindow = window.open(printUrl, '_blank');

            if (printWindow) {
                Swal.close();
            } else {
                Swal.fire('Popup Diblokir', 'Mohon izinkan popup untuk mencetak dokumen.', 'error');
            }
        }

        // Event listener untuk tombol print di dalam tabel
        $('#table-observasi tbody').on('click', '.btn-print', function() {
            const id = $(this).data('id');
            printObservasi(id);
        });
    });
</script>
<?= $this->endSection() ?>